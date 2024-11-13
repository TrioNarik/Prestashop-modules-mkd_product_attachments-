$(document).ready(function() {
    // =========================================================
    // Obsługa kliknięcia przycisku "Exportuj plik produktu" ===
    // ...ajaxExport
    // =========================================================
    $('#export').on('click', '.generateExportFile', function(e) {
        e.preventDefault(); // Zatrzymaj domyślną akcję
              
        let shopId = $(this).data('shop-id');
        let productId = $(this).data('product-id');

        // Utwórz obiekt FormData, aby przesłać dane w formie pliku
        let formData = new FormData();
        formData.append('shopId', shopId);
        formData.append('productId', productId);
        
        
        // Wyślij żądanie AJAX do aktualizacji załącznika
        $.ajax({
            type: 'POST',
            url: hookBackOfficeHeader_exportFiles.ajaxUrl + '&action=exportFile', // Kontroller Exportu zarejestrowany w hook'u
            data: formData,
            processData: false,
            contentType: false,

            success: function(data) {
                // Obsłuż odpowiedź z serwera
                if (data.success) {
                    showSuccessMessage(data.message);
                    window.location.hash = 'tab-hooks';
                    location.reload(); // Przeładuj stronę po udanym exporcie
                } else {
                    showErrorMessage(data.message);
                }
            },
            error: function(xhr, status, error) {
                // Obsłuż błędy AJAX i wyświetl komunikat o błędzie
                console.log('Błąd:', error);
                showErrorMessage(xhr.responseText);
                alert('Wystąpił błąd podczas wykonywania żądania AJAX.');
            }
        });

    });


    // ==========================================================
    // Obsługa kliknięcia przycisku "Usuń pliki Exportu" ========
    // ...ajaxExport
    // ==========================================================
    $('#export').on('click', '.deleteExportFile', function(e) {
        e.preventDefault(); // Zatrzymaj domyślną akcję przycisku

        // Pobierz dane z przycisku
        let deleteConfirmationMessage = $(this).data('confirm-message');

        let shopId = $(this).data('shop-id');
        let productId = $(this).data('product-id');
        let exportFormat = $(this).data('export-format');

        // Utwórz obiekt FormData, aby przesłać dane w formie pliku
        let formData = new FormData();
        formData.append('shopId', shopId);
        formData.append('productId', productId);
        formData.append('exportFormat', exportFormat);
        
        // Starsze wersję PS: =================================================
        // if (confirm(deleteConfirmationMessage) {

        // }

        modalConfirmation.create(deleteConfirmationMessage, null, {
            onContinue: function () {
    
                $.ajax({
                    type: 'POST',
                    url: hookBackOfficeHeader_exportFiles.ajaxUrl + '&action=deleteExportFileFormat', // Kontroller zarejestrowany w hook'u
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