(function (Drupal) {
  Drupal.behaviors.dxprCmsChoices = {
    attach: function (context, settings) {
      const selectElement = context.querySelector('.choices-select');
      // Degrade gracefully if Choices is not loaded.
      if (selectElement && typeof Choices !== 'undefined') {
        new Choices(selectElement, {
          removeItemButton: true,
        });
      }
    },
  };
})(Drupal);
