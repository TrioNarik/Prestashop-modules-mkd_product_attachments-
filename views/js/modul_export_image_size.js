// Ukrywanie select w formularzu konfiguracyjnym jeśli nie wybrano kolumny 'image' do exportu
// ==================================
$(document).ready(function() {
    // Znajdź checkbox i blok form-group.images
    var $imageCheckbox = $('input[type="checkbox"][value="image"]');
    var $imagesFormGroup = $('.form-group.images');

    // Nasłuchuj zmiany w checkboxie
    $imageCheckbox.change(function() {
        // Pokaż lub ukryj blok w zależności od stanu checkboxa
        $imagesFormGroup.toggle($imageCheckbox.is(':checked'));
    });

    // Inicjalnie ukryj blok, jeśli checkbox nie jest zaznaczony
    $imagesFormGroup.toggle($imageCheckbox.is(':checked'));
});



