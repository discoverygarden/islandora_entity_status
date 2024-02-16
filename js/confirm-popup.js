(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.confirmPopup = {
    attached: false, // Add a flag to track if the behavior is already attached.

    attach: function (context, settings) {
      // Check if the behavior is already attached.
      if (Drupal.behaviors.confirmPopup.attached) {
        return;
      }

      // Set the confirmation message from Drupal settings.
      var confirmationMessage = drupalSettings.custom_confirm_popup.message;

      // Flag to prevent recursion.
      var isClosingDialog = false;

      // Variable to store the submit button selector.
      var submitButton = $('.form-submit[value="Save"]');

      // Create a dialog box.
      var confirmDialog = $('<div></div>')
        .html(confirmationMessage)
        .dialog({
          autoOpen: false,
          modal: true,
          buttons: {
            Cancel: function () {
              submitButton.removeClass('submit-allowed');
              $(this).dialog('close');
            },
            Save: function () {
              // Close the dialog with a delay before triggering form submission.
              if (!isClosingDialog) {
                isClosingDialog = true;
                $(this).dialog('close');
                setTimeout(function () {
                  // Check if the submit button has a specific class.
                  if (submitButton.hasClass('submit-allowed')) {
                    // Trigger the form submission directly.
                    submitButton.click();
                  }
                  isClosingDialog = false;
                }, 30);
              }
            }
          }
        });

      // Attach the confirmation dialog to the node edit form submit button
      const elements = once('confirmPopup', '#node-islandora-object-edit-form [value=Save]', context);
      elements.forEach(function (element) {
        // Check if the form should be submitted.
        element.addEventListener('click', function(e) {
          if (element.classList.contains('submit-allowed')) {
            submitButton.click();
          } else {
            // Prevent the default form submission.
            e.preventDefault();
            element.classList.add('submit-allowed');
            // Open the dialog.
            confirmDialog.dialog('open');
          }
        });
      });

      // Mark the behavior as attached to prevent duplicate attachments.
      Drupal.behaviors.confirmPopup.attached = true;
    }
  };
})(jQuery, Drupal, drupalSettings);
