(function (Drupal, once) {
  Drupal.behaviors.dxprCmsChoices = {
    attach: function (context, settings) {
      if (typeof Choices === 'undefined') {
        return;
      }

      // Degrade gracefully if Choices is not loaded.
      once('init-choises', '.choices-select', context).forEach((element) => {
        new Choices(element, {
          removeItemButton: true,
        });
      });
    }
  }
}) (Drupal, once);
