/**
 * @file
 * Contains support code for end-to-end tests.
 */

import '@testing-library/cypress/add-commands';

const drupalMessages = '[data-drupal-messages]';

/**
 * Helper function to execute a system command in the test environment.
 *
 * Commands are run from the Cypress working directory which corresponds to the
 * $DDEV_DOCROOT directory.
 *
 * @param {string} command
 *   The command to execute.
 * @returns {Cypress.Chainable}
 *   The result of the command execution.
 */
const runCommand = (command) => {
  const { env, workingDir, user } = Cypress.env('execOptions') || {};

  if (user) {
    command = `sudo -u ${user} ${command}`;
  }
  if (workingDir) {
    command = `cd ${workingDir} && ${command}`;
  }

  return cy.exec(command, {
    env: env || {},
    timeout: 240000,
  });
};

Cypress.Commands.add('contextualLink', { prevSubject: true }, (subject, linkText) => {
  cy.wrap(subject)
    .should('have.class', 'contextual-region')
    .trigger('mouseover')
    .findByText('Open configuration options', { selector: 'button' })
    .click()
    .parent()
    .findByText(linkText, { selector: 'a' });
});

Cypress.Commands.add('dropButtonAction', { prevSubject: true }, (subject, actionText) => {
  cy.wrap(subject)
    .findByText('List additional actions', { selector: 'button > span' })
    .parent('button')
    .click()
    .closest('[data-once="dropbutton"]')
    .findByText(actionText, { selector: 'a' });
});

/**
 * Apply a Drupal recipe via `php core/scripts/drupal recipe` command.
 *
 * @param {string} [path]
 *   The path to the recipe. If none is provided apply the recipe that contains
 *   the current spec file.
 *
 * @usage
 * With no arguments, and assuming the file path
 * /dxpr_cms_seo_basic/tests/e2e/spec.cy.js, this will apply the
 * dxpr_cms_seo_basic recipe.
 *
 * cy.applyRecipe();
 *
 * Specify the path of the recipe to apply.
 *
 * cy.applyRecipe('/path/to/recipe');
 */
Cypress.Commands.add('applyRecipe', (path, inputs) => {
  path ??= Cypress.spec.absolute.split('/').slice(0, -3);

  // If any input values were passed, convert them to command-line options.
  const options = Object.entries(inputs || {})
    .map(([key, value]) => `--input=${ path.at(-1) }.${key}=${value}`)
    .join(' ');

  Cypress.log({
    name: 'applyRecipe',
    displayName: 'recipe',
    message: 'Apply recipe',
    consoleProps() {
      return { path: path, inputs: inputs };
    },
  });
  runCommand(`php core/scripts/drupal recipe ${ path.join('/') } ${options} --no-interaction`);
});

/**
 * Install Drupal using the `php core/scripts/test-site.php install` command.
 *
 * @param {string} [installProfile]
 *   Optional installation profile to use for Drupal setup.
 *
 * @usage
 * cy.setUp('standard');
 */
Cypress.Commands.add('setUp', (installProfile) => {
  let command = `php core/scripts/test-site.php install --json`;
  if (installProfile) {
    command += ` --install-profile=${installProfile}`;
  }

  const setupFile = Cypress.env('testSiteSetupFile');
  if (setupFile) {
    command += ` --setup-file=${setupFile}`;
  }

  const initSession = () => {
    Cypress.log({
      name: 'installDrupal',
      displayName: 'install',
      message: `Install Drupal from ${installProfile || 'default'} profile`,
    });

    runCommand(command).then((result) => {
      const { db_prefix, user_agent, site_path } = JSON.parse(result.stdout);

      // Save database prefix for `tearDown` command.
      Cypress.env('dbPrefix', db_prefix);

      // Ensure that commands which get the site path from the
      // DRUPAL_DEV_SITE_PATH environment variable use the test site's path.
      const execOptions = Cypress.env('execOptions') || {};
      if ('env' in execOptions) {
        assert(typeof execOptions.env === 'object');
        execOptions.env['DRUPAL_DEV_SITE_PATH'] = site_path;
        Cypress.env('execOptions', execOptions);
      }

      // Set a cookie to ensure that visits to the test site will be directed to
      // a version of the site running in a test database.
      cy.setCookie('SIMPLETEST_USER_AGENT', encodeURIComponent(user_agent), {
        domain: new URL(Cypress.config('baseUrl')).host,
        path: '/',
      });
    });
  };
  cy.session(installProfile || 'testSite', initSession, {
    validate () {
      cy.getCookie('SIMPLETEST_USER_AGENT').should('not.be.empty');
    },
  });
});

/**
 * Tear down the Drupal test environment created by setUp().
 *
 * @example
 * cy.tearDown();
 */
Cypress.Commands.add('tearDown', () => {
  const dbPrefix = Cypress.env('dbPrefix');
  expect(dbPrefix).to.not.be.empty;

  runCommand(`php core/scripts/test-site.php tear-down ${dbPrefix}`);
});

/**
 * Log in as a specific Drupal user by username.
 *
 * Account password is hard-coded to "password". See drupalCreateUser below.
 *
 * @param {string} name
 *   The username to log in with.
 *
 * @example
 * cy.drupalLogin('admin');
 */
Cypress.Commands.add('drupalLogin', (name) => {
  Cypress.log({
    name: 'drupalLogin',
    displayName: 'login',
    message: `Logging in as ${name}`,
  });
  cy.visit('/user/login');
  cy.get('input[name="name"]').type(name);
  cy.findByLabelText('Password').type('password');
  cy.findByDisplayValue('Log in').click();
  cy.get('.page-title').should('contain.text', name);
});

/**
 * Log out the currently logged-in user.
 *
 * @example
 * cy.drupalLogout();
 */
Cypress.Commands.add('drupalLogout', () => {
  cy.visit('/user/logout');
  cy.findByDisplayValue('Log out').click();
});

/**
 * Create a new Drupal user account with a specific username and optional roles.
 *
 * New account password is hard-coded to "password".
 *
 * @param {string} name
 *   The username for the new account.
 * @param {Array<string>} [roles=[]]
 *   An array of role names to assign to the new user.
 *
 * @example
 * cy.drupalCreateUser('testuser', ['editor']);
 */
Cypress.Commands.add('drupalCreateUser', (name, roles = []) => {
  cy.visit('/admin/people/create');

  cy.findByLabelText('Email address').type(`${name}@cypress.local`);
  cy.findByLabelText('Username').type(name);
  cy.findByLabelText('Password').type('password');
  cy.findByLabelText('Confirm password').type('password');
  cy.get('input[name^="roles["]').check(roles);
  cy.findByDisplayValue('Create new account').click();
  cy.get(drupalMessages).should('contain.text', `Created a new user account for ${name}.`);
});
