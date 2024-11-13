// =========================================
// Funkcja do ustawiania atrybutu disabled
// dla przycisków gdy field nie ma nazwy
// =========================================
function setSaveButtonState(buttonSelector) {
    // Znajdź przyciski
    let saveButtons = $(buttonSelector);

    // Iteruj przez każdy przycisk "Save"
    saveButtons.each(function () {
        // Pobierz fieldId z danego przycisku
        let fieldId = $(this).data('field-id');

        // Sprawdź, czy przynajmniej jeden input ma disabled
        let isDisabled = false;

        // Znajdź wszystkie inputy
        $('[id^=extra_field_' + fieldId + '_value]').each(function () {
            // Sprawdź, czy input jest zablokowany
            if ($(this).prop('disabled')) {
                isDisabled = true;
                return false; // Przerwij pętlę, bo już jeden input jest zablokowany
            }
        });

        // Ustaw przycisk na "Disabled"
        $(this).prop('disabled', isDisabled);

    });
}

$(document).ready(function() {
    
    // Sprawdź dostępność przycisku jeśli input nie ma nazwy
    setSaveButtonState('.submitField');

    // =========================================================
    // Obsługa kliknięcia przycisku "Zapisz" lub "Update" Value Extra Field
    // =========================================================
    $('#extra_fields').on('click', '.submitField, .updateField', function(e) {
        e.preventDefault(); // Zatrzymaj domyślną akcję
            
        let shopId      = $(this).data('shop-id');
        let productId   = $(this).data('product-id');
        let fieldId     = $(this).data('field-id');
        let action      = $(this).data('action'); // Dodaj akcję

        let errorEmpty  = $(this).data('error-message'); // Puste pola => error
            

        // Sprawdź, czy wszystkie pola tekstowe są puste
        let isEmpty = true;

        // Przechowuje wartość pierwszego inputa (jeśli istnieje)
        let firstValue = null;

        // Zmienna do przechowywania danych dla każdego języka
        let languageData = {};

        // Znajdź wszystkie inputy, których ID zaczyna się od 'extra_field_{fieldId}_value'
        $('[id^=extra_field_' + fieldId + '_value]').each(function(index) {
            let langId = $(this).data('lang-id');
            let value = $(this).val();

            // Sprawdź, czy wartość pola tekstowego jest pusta
            if (value.trim() !== '') {
                isEmpty = false;

                // Jeśli to pierwszy input, zapisz jego wartość
                if (index === 0) {
                    firstValue = value;
                }

                // Dodaj wartość do languageData
                languageData[langId] = value;
            }
        });

        // Sprawdź, czy pola tekstowe są puste
        if (isEmpty) {
            showErrorMessage(errorEmpty);
            return; // Zatrzymaj, jeśli wszystkie pola są puste
        }

        // Przypisz wartość pierwszego inputa do pozostałych inputów (jeśli istnieje)
        if (firstValue !== null) {
            $('[id^=extra_field_' + fieldId + '_value]').each(function() {
                let langId = $(this).data('lang-id');
                $(this).val(languageData[langId] || firstValue);
            });
        }
    
        // Utwórz obiekt FormData, aby przesłać dane
        let formData = new FormData();
        formData.append('shopId', shopId);
        formData.append('productId', productId);
        formData.append('fieldId', fieldId);
        formData.append('action', action); // Dodaj akcję
        // Wersję językowe
        for (let langId in languageData) {
            formData.append('fieldValue[' + langId + ']', languageData[langId]);
        }

        console.log(languageData);
        
        $.ajax({
            type: 'POST',
            url: hookBackOfficeHeader_extraFields.ajaxUrl + '&action=' + action, // Użyj zmiennej action
            data: formData,
            processData: false,
            contentType: false,

            success: function(data) {
                // Obsłuż odpowiedź z serwera
                if (data.success) {
                    showSuccessMessage(data.message);
                    
                    window.location.hash = 'tab-hooks';
                    location.reload(); // Przeładuj stronę po udanej akcji
                } else {
                    
                    showErrorMessage(data.message);
                }
            },
            error: function(xhr, status, error) {
                // Obsłuż błędy AJAX i wyświetl komunikat o błędzie
                console.log('Błąd:', error);
                alert('Wystąpił błąd podczas wykonywania żądania AJAX.');
            }
        });
    });



    // ==========================================================
    // Obsługa kliknięcia przycisku "Status Extra Field" ========
    // ==========================================================
    $('#extra_fields').on('click', '.statusField', function(e) {
        e.preventDefault(); // Zatrzymaj domyślną akcję przycisku

        // Pobierz dane z przycisku
        let statusConfirmationMessage = $(this).data('confirm-message');

        let productFieldId = $(this).data('product-field-id')

        // Utwórz obiekt FormData, aby przesłać dane w formie pliku
        let formData = new FormData();
        formData.append('productFieldId', productFieldId);
        
        
        // Starsze wersje PS: =================================================
        // if (confirm(deleteConfirmationMessage) {

        // }

        modalConfirmation.create(statusConfirmationMessage, null, {
            onContinue: function () {
    
                $.ajax({
                    type: 'POST',
                    url: hookBackOfficeHeader_extraFields.ajaxUrl + '&action=changeStatusField', // Kontroller zarejestrowany w hook'u
                    data: formData,
                    processData: false,
                    contentType: false,
        
                    success: function(data) {
                        // Obsłuż odpowiedź z serwera
                        if (data.success) {
                            showSuccessMessage(data.message);
                            window.location.hash = 'tab-hooks';
                            location.reload(); // Przeładuj stronę po udanej akcji
                        } else {
                            // Wystąpił błąd podczas usuwania plików
                            showErrorMessage(data.message);
                            console.log('Data received from the server:', data);
                            console.error('Error:', data.message);                            
                        }
                    },
                    error: function(xhr, status, error) {
                        // Obsłuż błędy AJAX, np. wyświetl komunikat o błędzie
                        // console.error('Error:', error);
                        console.error('Error:', xhr.status, xhr.statusText, xhr.responseText);
                        alert('Wystąpił błąd podczas wykonywania żądania AJAX.');
                    }
                });
    
            }
        }).show();
    });

});