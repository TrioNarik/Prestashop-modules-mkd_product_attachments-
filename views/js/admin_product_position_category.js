$(document).ready(function() {
    // =========================================================
    // Obsługa kliknięcia przycisku "Pozycja produktu" ===
    // ...ajaxExport
    // =========================================================
    $('#position').on('click', '.submitPosition', function(e) {
        e.preventDefault(); // Zatrzymaj domyślną akcję
              
        let shopId = $(this).data('shop-id');
        let langId = $(this).data('lang-id');
        let productId = $(this).data('product-id');
        let categoryId = $(this).data('category-id');
        let position = document.getElementById('product_position').value;

        alert(position);


        // Utwórz obiekt FormData, aby przesłać dane w formie pliku
        let formData = new FormData();
        formData.append('shopId', shopId);
        formData.append('langId', langId);
        formData.append('productId', productId);
        formData.append('categoryId', categoryId);
        formData.append('position', position);
        
        // Wyślij żądanie AJAX do aktualizacji załącznika
        $.ajax({
            type: 'POST',
            url: hookBackOfficeHeader_positionProduct.ajaxUrl + '&action=saveProductPosition', // Kontroller Exportu zarejestrowany w hook'u
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

});