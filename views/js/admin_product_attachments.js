// Funkcja do pobierania dozwolonych rozszerzeń plików na podstawie Grupy załączników
// wersja testowa (!!! nie podpięta)
function getAllowedExtensions(groupId, callback) {
    // AJAX do wczytania pliku JSON
    $.ajax({
        url: hookBackOfficeHeader_attachments.jsonSampleDataFile,
        dataType: 'json',
        async: false, // Ustawiamy na synchroniczne wczytywanie
        success: function(data) {
            const attachmentFormats = data.attachmentFormats;

            // Szukamy danych dla danej grupy załączników
            const groupData = attachmentFormats.find(group => group.value === groupId);

            if (groupData) {
                const allowedExtensions = groupData.allowedExtensions || [];
                callback(allowedExtensions); // Wywołujemy funkcję zwrotną z listą dozwolonych rozszerzeń
            }
        },
        error: function() {
            console.error('Błąd podczas wczytywania pliku JSON.');
        }
    });
}

// Funkcja do obsługi zmiany typu pola w zależności od groupURL
function configureInputField(groupURL, inputValue) {

    let inputFieldType = $('#inputFiedType');

    if (groupURL === 1) {
        // Ustaw atrybuty dla URL
        inputFieldType.prop('type', 'text');
        inputFieldType.prop('placeholder', 'http://...');
        inputFieldType.prop('maxlength', '250');
        inputFieldType.val(inputValue);
        $('#attachmentFileURL').hide(); // Załączony plik
        
    } else {
        // Ustaw atrybuty dla File
        inputFieldType.prop('type', 'file');
    }
}



$(document).ready(function() {

    $('#modalGroupId').text('');

    $('.edit-attachment').on('click', function(e) {
        e.preventDefault();

        // Usuń atrybut required
        $('#required').hide();
        $('#attachmentFile').attr('required', false);
        $('#inputFiedType').closest('.form-group').removeClass('has-danger');
        
        let groupId = $(this).data('group-id');
        let langId =  $(this).data('attachment-lang-id');
        let attachmentId = $(this).data('attachment-id');
        let attachmentName = $(this).data('attachment-name');
        let attachmentComment = $(this).data('attachment-comment');
        let attachmentStatus = $(this).data('attachment-status');
        
        let groupURL = $(this).data('group-url');

        let attachmentFileURL = $(this).data('attachment-file');
        
        let titleFile = attachmentFileURL.replace(/^\d+-\d+-\d+_\d+_/, '');

        configureInputField(groupURL, attachmentFileURL);
        
        // Wypełnij formularz danymi o załączniku
        $('#modalGroupId').text(groupId);
        $('#attachmentModal').find('#groupId').val(groupId);
        $('#attachmentLang').val(langId);
        $('#attachmentId').val(attachmentId);
        $('#attachmentName').val(attachmentName);
        $('#attachmentComment').val(attachmentComment);
        $('#active').prop('checked', attachmentStatus == 1);

        $('#attachmentFileURL').text(titleFile).addClass('alert alert-success');

        // Zamień button "Save" na "Update"
        let submitButton = $('#attachmentModal').find('.submitAttachment');
        submitButton.removeClass('btn-primary').removeClass('submitAttachment').addClass('btn-warning').addClass('updateAttachment');
        submitButton.attr('data-attachment-id', attachmentId); // Dodaj ID załącznika

        
        // Otwórz modal
        $('#attachmentModal').modal('show');

    });


    // Obsługa zamknięcia modala
    $('#attachmentModal').on('hidden.bs.modal', function () {
        
        $('#modalGroupId').text('');
        $('#attachmentFileURL').show(); //Załączony plik

        let submitButton = $('#attachmentModal').find('.updateAttachment');
        submitButton.removeAttr('data-attachment-id');
        submitButton.removeClass('btn-warning').removeClass('updateAttachment').addClass('btn-primary').addClass('submitAttachment');
    });


    $('.add-attachment').on('click', function() {

        $('#inputFiedType').closest('.form-group').removeClass('has-danger');
        
        // Pobierz aktualną Grupę załączników
        let groupId = $(this).data('group-id');
        $('#modalGroupId').text(groupId);
        
        // Pobierz typ Input
        let groupURL = $(this).data('group-url');
        
        configureInputField(groupURL, '');

        // Dodaj atrybut required
        $('#required').show();
        $('#attachmentFile').attr('required', true);

        // Wyczyść wartości pól formularza
        $('#attachmentName').val('');
        $('#attachmentComment').val('');
        $('#attachmentFile').val('');
        $('#active').prop('checked', true);
        $('#attachmentFileURL').text('').removeClass('alert alert-success');

        // Ustaw wartość groupId w modalu
        $('#attachmentModal').find('#groupId').val(groupId);

        // Przywróć przycisk "Save" do początkowego stanu
        let submitButton = $('#attachmentModal').find('.updateAttachment');
        submitButton.removeClass('btn-warning').removeClass('updateAttachment').addClass('btn-primary').addClass('submitAttachment');

        // Otwórz modal
        $('#attachmentModal').modal('show');
    });



    // =========================================================
    // Obsługa kliknięcia przycisku "Zapisz"
    // ...ajaxUrl
    // =========================================================
    $('#attachmentModal').on('click', '.submitAttachment', function(e) {
        e.preventDefault(); // Zatrzymaj domyślną akcję przycisku submit

        // Pobierz dane z formularza
        let shopId = $('#shopId').val();
        let langId = $('#attachmentLang').val();
        let groupId = $('#groupId').val();
        let productId = $('#productId').val();
        let attachmentName = $('#attachmentName').val();
        let attachmentComment = $('#attachmentComment').val();
        
        let active = $('#active').prop('checked') ? 1 : 0;

        let inputType = $('#inputFiedType').prop('type');

        // Utwórz obiekt FormData, aby przesłać dane w formie pliku
        let formData = new FormData();
        formData.append('shopId', shopId);
        formData.append('langId', langId);
        formData.append('groupId', groupId);
        formData.append('productId', productId);
        formData.append('attachmentName', attachmentName);
        formData.append('attachmentComment', attachmentComment);
        
        formData.append('active', active);

        // Sprawdź, typ Input i empty
        if (inputType === 'file') {
            // Jeśli input FILE, pobierz plik
            let attachmentFile = $('#inputFiedType')[0].files[0];
            if (!attachmentFile) {
                $('#inputFiedType').closest('.form-group').addClass('has-danger');
                return;
                
            } else {
                $('#inputFiedType').closest('.form-group').removeClass('has-danger');
            }

            formData.append('attachmentFile', attachmentFile);

        } else {
            // Jeśli input TEXT, pobierz wartość
            let attachmentURL = $('#inputFiedType').val().trim();
            if (attachmentURL === '') {
                $('#inputFiedType').closest('.form-group').addClass('has-danger');
                return;
            } else {
                $('#inputFiedType').closest('.form-group').removeClass('has-danger');
            }

            formData.append('attachmentURL', attachmentURL);
        }
        
        $.ajax({
            type: 'POST',
            url: hookBackOfficeHeader_attachments.ajaxUrl + '&action=addAttachment', // Kontroller zarejestrowany w hook'u
            data: formData,
            processData: false,
            contentType: false,

            success: function(data) {
                // Obsłuż odpowiedź z serwera
                if (data.success) {

                    $('#attachmentModal').modal('hide');

                    showSuccessMessage(data.message); // w kontrolerze $response

                    window.location.hash = 'tab-hooks';
                    
                    // Przeładuj stronę
                    location.reload();

                }
            },
            error: function(xhr, status, error, data) {
                // Obsłuż błędy AJAX i wyświetl komunikat o błędzie
                console.log('Error:', error);
                console.log('Error details:', xhr.responseText);
                alert('Wystąpił błąd podczas wykonywania żądania AJAX.');
                
            }
        });
    });


    // =========================================================
    // Obsługa kliknięcia przycisku "Update"
    // ...ajaxUrl
    // =========================================================
    $('#attachmentModal').on('click', '.updateAttachment', function(e) {
        e.preventDefault(); // Zatrzymaj domyślną akcję przycisku submit

        // Pobierz dane z formularza
        let shopId = $('#shopId').val();
        let langId = $('#attachmentLang').val();
        let groupId = $('#groupId').val();
        let productId = $('#productId').val();
        let attachmentId = $(this).data('attachment-id'); // Pobierz ID załącznika
        let attachmentName = $('#attachmentName').val();
        let attachmentComment = $('#attachmentComment').val();

        let active = $('#active').prop('checked') ? 1 : 0;

        let inputType = $('#inputFiedType').prop('type');

        let currentFile = $('#attachmentFileURL').text();

        // let attachmentFile = $('#attachmentFile')[0].files[0] ? $('#attachmentFile')[0].files[0] : $('#attachmentFileURL').text();

        // Utwórz obiekt FormData, aby przesłać dane w formie pliku
        let formData = new FormData();
        formData.append('shopId', shopId);
        formData.append('groupId', groupId);
        formData.append('langId', langId);
        formData.append('productId', productId);
        formData.append('attachmentId', attachmentId); // Przekaż ID załącznika
        formData.append('attachmentName', attachmentName);
        formData.append('attachmentComment', attachmentComment);
        formData.append('active', active);

        // formData.append('attachmentFile', attachmentFile);

        // Sprawdź, typ Input i empty
        if (inputType === 'file') {
            // Jeśli input FILE, pobierz plik
            let attachmentFile = $('#inputFiedType')[0].files[0];

            formData.append('attachmentFile', attachmentFile);
            
        } else {
            // Jeśli input TEXT, pobierz wartość
            let attachmentURL = $('#inputFiedType').val().trim();

            if (attachmentURL === '') {
                $('#inputFiedType').closest('.form-group').addClass('has-danger');
                return;
            } else {
                $('#inputFiedType').closest('.form-group').removeClass('has-danger');
            }

            formData.append('attachmentURL', attachmentURL);
        }

        // Wyślij żądanie AJAX do aktualizacji załącznika
        $.ajax({
            type: 'POST',
            url: hookBackOfficeHeader_attachments.ajaxUrl + '&action=updateAttachment', // Kontroller zarejestrowany w hook'u
            data: formData,
            processData: false,
            contentType: false,

            success: function(data) {
                // Obsłuż odpowiedź z serwera
                if (data.success) {
                    $('#attachmentModal').modal('hide');
                    showSuccessMessage(data.message);
                    window.location.hash = 'tab-hooks';
                    location.reload(); // Przeładuj stronę po udanej aktualizacji
                } else {
                    showErrorMessage(data.message);
                }
            },
            error: function(xhr, status, error) {
                // Obsłuż błędy AJAX i wyświetl komunikat o błędzie
                console.log('Błąd:', error);
                 // Wyświetl zawartość formData 
                alert('Wystąpił błąd podczas wykonywania żądania AJAX.');
            }
        });
    });


    // =========================================================
    // Obsługa kliknięcia przycisku "Delete"
    // ...ajaxUrl
    // =========================================================
    $('.deleteAttachment').on('click', function(e) {
        e.preventDefault(); // Zatrzymaj domyślną akcję przycisku

        // Pobierz dane z przycisku
        let deleteConfirmationMessage = $(this).data('confirm-message');

        let shopId = $(this).data('shop-id');
        let groupId = $(this).data('group-id');
        let productId = $(this).data('product-id');
        let attachmentId = $(this).data('attachment-id');

        // Utwórz obiekt FormData, aby przesłać dane w formie pliku
        let formData = new FormData();
        formData.append('shopId', shopId);
        formData.append('groupId', groupId);
        formData.append('productId', productId);
        formData.append('attachmentId', attachmentId);
        

        // Starsze wersję PS: =================================================
        // if (confirm(deleteConfirmationMessage) {

        // }

        
        modalConfirmation.create(deleteConfirmationMessage, null, {
            onContinue: function () {
    
                $.ajax({
                    type: 'POST',
                    url: hookBackOfficeHeader_attachments.ajaxUrl + '&action=deleteAttachment', // Kontroller zarejestrowany w hook'u
                    data: formData,
                    processData: false,
                    contentType: false,
        
                    success: function(data) {
                        // Obsłuż odpowiedź z serwera
                        if (data.success) {
                            showSuccessMessage(data.message);
                            window.location.hash = 'tab-hooks';
                            location.reload(); // Przeładuj stronę po udanej aktualizacji
                        } else {
                            // Wystąpił błąd podczas usuwania załącznika
                            showErrorMessage(data.message);
                            console.error('Błąd:', data.message);
                            // alert('Wystąpił błąd podczas usuwania załącznika: ' + data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Obsłuż błędy AJAX, np. wyświetl komunikat o błędzie
                        console.error('Błąd:', error);
                        console.log('Error details:', xhr.responseText);
                        alert('Wystąpił błąd podczas wykonywania żądania AJAX.');
                    }
                });
    
            }
        }).show();
    });

});