jQuery(document).ready(function($) {
    'use strict';

    // Variáveis globais
    let currentModal = null;
    let currentProponentId = null;

    // Inicializar componentes
    initModals();
    initForms();
    initTables();
    initFileUploads();

    // Inicializar modais
    function initModals() {
        $('.ft1-modal-trigger').on('click', function(e) {
            e.preventDefault();
            const modalId = $(this).data('modal');
            
            // Se for modal de documentos, salvar o ID do proponente
            if (modalId === 'ft1-documents-modal') {
                currentProponentId = $(this).data('proponent-id');
                loadDocuments(currentProponentId);
            }
            
            openModal(modalId);
        });

        $('.ft1-close, .ft1-modal').on('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        $(document).keyup(function(e) {
            if (e.keyCode === 27 && currentModal) {
                closeModal();
            }
        });
    }

    function openModal(modalId) {
        currentModal = $('#' + modalId);
        if (currentModal.length) {
            currentModal.fadeIn(300);
            $('body').addClass('modal-open');
        }
    }

    function closeModal() {
        if (currentModal) {
            currentModal.fadeOut(300);
            currentModal = null;
            currentProponentId = null;
            $('body').removeClass('modal-open');
            
            // Limpar formulários
            $('.ft1-form')[0].reset();
            $('.ft1-form input[type="hidden"]').val('');
        }
    }

    // Inicializar formulários
    function initForms() {
        // Formulário de edital
        $('#ft1-edital-form').on('submit', function(e) {
            e.preventDefault();
            saveEdital();
        });

        // Formulário de proponente
        $('#ft1-proponent-form').on('submit', function(e) {
            e.preventDefault();
            saveProponent();
        });

        // Formulário de contrato
        $('#ft1-contract-form').on('submit', function(e) {
            e.preventDefault();
            saveContract();
        });
    }

    function saveEdital() {
        const form = $('#ft1-edital-form');
        const formData = form.serialize() + '&action=ft1_save_edital&nonce=' + ft1_ajax.nonce;

        showLoading();

        $.post(ft1_ajax.ajax_url, formData)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', response.data.message);
                    closeModal();
                    location.reload();
                } else {
                    showAlert('error', response.data.message);
                }
            })
            .fail(function() {
                hideLoading();
                showAlert('error', 'Erro de conexão. Tente novamente.');
            });
    }

    function saveProponent() {
        const form = $('#ft1-proponent-form');
        const formData = form.serialize() + '&action=ft1_save_proponent&nonce=' + ft1_ajax.nonce;

        showLoading();

        $.post(ft1_ajax.ajax_url, formData)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', response.data.message);
                    closeModal();
                    location.reload();
                } else {
                    showAlert('error', response.data.message);
                }
            })
            .fail(function() {
                hideLoading();
                showAlert('error', 'Erro de conexão. Tente novamente.');
            });
    }

    function saveContract() {
        const form = $('#ft1-contract-form');
        const formData = form.serialize() + '&action=ft1_save_contract&nonce=' + ft1_ajax.nonce;

        showLoading();

        $.post(ft1_ajax.ajax_url, formData)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', response.data.message);
                    closeModal();
                    location.reload();
                } else {
                    showAlert('error', response.data.message);
                }
            })
            .fail(function() {
                hideLoading();
                showAlert('error', 'Erro de conexão. Tente novamente.');
            });
    }

    // Inicializar tabelas
    function initTables() {
        $('.ft1-table').each(function() {
            const table = $(this);
            
            // Adicionar funcionalidade de busca se existir input de busca
            const searchInput = table.siblings('.ft1-search');
            if (searchInput.length) {
                searchInput.on('input', function() {
                    filterTable(table, $(this).val());
                });
            }

            // Adicionar ordenação nas colunas
            table.find('th[data-sort]').on('click', function() {
                const column = $(this).data('sort');
                sortTable(table, column);
            });
        });

        // Botões de ação na tabela
        $('.ft1-btn-edit').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const type = $(this).data('type');
            editItem(type, id);
        });

        $('.ft1-btn-delete').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const type = $(this).data('type');
            if (confirm('Tem certeza que deseja excluir este item?')) {
                deleteItem(type, id);
            }
        });

        $('.ft1-btn-send-email').on('click', function(e) {
            e.preventDefault();
            const contractId = $(this).data('contract-id');
            sendContract(contractId, 'email');
        });

        $('.ft1-btn-send-whatsapp').on('click', function(e) {
            e.preventDefault();
            const contractId = $(this).data('contract-id');
            sendContract(contractId, 'whatsapp');
        });

        // Delegação de eventos para botões dinâmicos
        $(document).on('click', '.ft1-btn-delete-document', function(e) {
            e.preventDefault();
            const documentId = $(this).data('document-id');
            if (confirm('Tem certeza que deseja excluir este documento?')) {
                deleteDocument(documentId);
            }
        });
    }

    function filterTable(table, searchTerm) {
        const rows = table.find('tbody tr');
        
        rows.each(function() {
            const row = $(this);
            const text = row.text().toLowerCase();
            
            if (text.includes(searchTerm.toLowerCase())) {
                row.show();
            } else {
                row.hide();
            }
        });
    }

    function sortTable(table, column) {
        const tbody = table.find('tbody');
        const rows = tbody.find('tr').toArray();

        rows.sort(function(a, b) {
            const aVal = $(a).find(`td[data-sort="${column}"]`).text();
            const bVal = $(b).find(`td[data-sort="${column}"]`).text();
            
            return aVal.localeCompare(bVal);
        });

        tbody.empty().append(rows);
    }

    function editItem(type, id) {
        const data = {
            action: `ft1_get_${type}`,
            id: id,
            nonce: ft1_ajax.nonce
        };

        showLoading();

        $.post(ft1_ajax.ajax_url, data)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    populateEditForm(type, response.data);
                    openModal(`ft1-${type}-modal`);
                } else {
                    showAlert('error', 'Erro ao carregar dados para edição.');
                }
            })
            .fail(function() {
                hideLoading();
                showAlert('error', 'Erro de conexão. Tente novamente.');
            });
    }

    function populateEditForm(type, data) {
        const form = $(`#ft1-${type}-form`);
        
        // Preencher campos do formulário
        Object.keys(data).forEach(function(key) {
            const field = form.find(`[name="${key}"]`);
            if (field.length) {
                if (key === 'value' && data[key]) {
                    // Formatar valor monetário
                    field.val('R$ ' + parseFloat(data[key]).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1."));
                } else {
                    field.val(data[key]);
                }
            }
        });
        
        // Atualizar título do modal
        const modalTitle = form.closest('.ft1-modal-content').find('.ft1-modal-header h2');
        modalTitle.text(modalTitle.text().replace('Cadastrar', 'Editar').replace('Criar', 'Editar'));
    }

    function deleteItem(type, id) {
        const data = {
            action: `ft1_delete_${type}`,
            id: id,
            nonce: ft1_ajax.nonce
        };

        showLoading();

        $.post(ft1_ajax.ajax_url, data)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', 'Item excluído com sucesso!');
                    location.reload();
                } else {
                    showAlert('error', 'Erro ao excluir item.');
                }
            })
            .fail(function() {
                hideLoading();
                showAlert('error', 'Erro de conexão. Tente novamente.');
            });
    }

    function sendContract(contractId, method) {
        const data = {
            action: 'ft1_send_contract',
            contract_id: contractId,
            method: method,
            nonce: ft1_ajax.nonce
        };

        showLoading();

        $.post(ft1_ajax.ajax_url, data)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', response.data.message);
                    
                    if (method === 'whatsapp' && response.data.whatsapp_url) {
                        window.open(response.data.whatsapp_url, '_blank');
                    }
                    
                    location.reload();
                } else {
                    showAlert('error', response.data.message);
                }
            })
            .fail(function() {
                hideLoading();
                showAlert('error', 'Erro de conexão. Tente novamente.');
            });
    }

    // Inicializar upload de arquivos
    function initFileUploads() {
        $('.ft1-file-upload').on('click', function() {
            $(this).find('input[type="file"]').click();
        });

        $('.ft1-file-upload input[type="file"]').on('change', function() {
            const input = this;
            
            if (input.files && input.files[0] && currentProponentId) {
                uploadDocument(input.files[0], currentProponentId);
            }
        });
    }

    function uploadDocument(file, proponentId) {
        const formData = new FormData();
        formData.append('document', file);
        formData.append('proponent_id', proponentId);
        formData.append('action', 'ft1_upload_document');
        formData.append('nonce', ft1_ajax.nonce);

        showLoading();

        $.ajax({
            url: ft1_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', response.data.message);
                    loadDocuments(proponentId);
                } else {
                    showAlert('error', response.data.message);
                }
            },
            error: function() {
                hideLoading();
                showAlert('error', 'Erro ao fazer upload do documento.');
            }
        });
    }

    function loadDocuments(proponentId) {
        const data = {
            action: 'ft1_get_documents',
            proponent_id: proponentId,
            nonce: ft1_ajax.nonce
        };

        $.post(ft1_ajax.ajax_url, data)
            .done(function(response) {
                if (response.success) {
                    displayDocuments(response.data);
                } else {
                    $('#documents-list').html('<p>Erro ao carregar documentos.</p>');
                }
            })
            .fail(function() {
                $('#documents-list').html('<p>Erro de conexão ao carregar documentos.</p>');
            });
    }

    function displayDocuments(documents) {
        let html = '';
        
        if (documents && documents.length > 0) {
            html += '<h4>📎 Documentos Enviados</h4>';
            html += '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">';
            
            documents.forEach(function(doc) {
                const fileExt = doc.filename.split('.').pop().toLowerCase();
                let icon = '📄';
                
                if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                    icon = '🖼️';
                } else if (fileExt === 'pdf') {
                    icon = '📕';
                } else if (['doc', 'docx'].includes(fileExt)) {
                    icon = '📘';
                }
                
                const uploadDate = new Date(doc.uploaded_at).toLocaleDateString('pt-BR');
                
                html += `
                    <div style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; text-align: center; background: #f9f9f9;">
                        <div style="font-size: 32px; margin-bottom: 10px;">${icon}</div>
                        <div style="font-size: 12px; font-weight: bold; margin-bottom: 5px;">${doc.filename}</div>
                        <div style="font-size: 11px; color: #666; margin-bottom: 10px;">${uploadDate}</div>
                        <div>
                            <a href="${doc.file_path}" target="_blank" class="ft1-button" style="font-size: 11px; padding: 5px 10px; margin-right: 5px;">👁️ Ver</a>
                            <button class="ft1-button secondary ft1-btn-delete-document" data-document-id="${doc.id}" style="font-size: 11px; padding: 5px 10px;">🗑️ Excluir</button>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
        } else {
            html = '<p style="text-align: center; color: #666; margin: 20px 0;">Nenhum documento enviado ainda.</p>';
        }
        
        $('#documents-list').html(html);
    }

    function deleteDocument(documentId) {
        const data = {
            action: 'ft1_delete_document',
            id: documentId,
            nonce: ft1_ajax.nonce
        };

        showLoading();

        $.post(ft1_ajax.ajax_url, data)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', response.data.message);
                    if (currentProponentId) {
                        loadDocuments(currentProponentId);
                    }
                } else {
                    showAlert('error', response.data.message);
                }
            })
            .fail(function() {
                hideLoading();
                showAlert('error', 'Erro de conexão. Tente novamente.');
            });
    }

    // Funções utilitárias
    function showLoading() {
        if ($('#ft1-loading').length === 0) {
            $('body').append(`
                <div id="ft1-loading" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; display: flex; align-items: center; justify-content: center;">
                    <div style="background: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <div style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #0073aa; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 10px;"></div>
                        <p>Carregando...</p>
                    </div>
                </div>
                <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                </style>
            `);
        }
        $('#ft1-loading').show();
    }

    function hideLoading() {
        $('#ft1-loading').hide();
    }

    function showAlert(type, message) {
        const alert = $(`<div class="ft1-alert ${type}" style="margin: 20px 0; padding: 15px; border-radius: 4px;">${message}</div>`);
        
        // Remover alertas existentes
        $('.ft1-alert').remove();
        
        // Adicionar novo alerta
        $('.ft1-cultural-admin').prepend(alert);
        
        // Auto-remover após 5 segundos
        setTimeout(function() {
            alert.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Máscara para campos
    $('input[data-mask="phone"]').on('input', function() {
        let value = this.value.replace(/\D/g, '');
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        this.value = value;
    });

    $('input[data-mask="cpf"]').on('input', function() {
        let value = this.value.replace(/\D/g, '');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.value = value;
    });

    $('input[data-mask="cnpj"]').on('input', function() {
        let value = this.value.replace(/\D/g, '');
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
        this.value = value;
    });

    $('input[data-mask="money"]').on('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value === '') {
            this.value = '';
            return;
        }
        value = (value / 100).toFixed(2) + '';
        value = value.replace(".", ",");
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        this.value = 'R$ ' + value;
    });

    // Limpar formulários ao abrir modal para novo item
    $('.ft1-modal-trigger').on('click', function() {
        const modalId = $(this).data('modal');
        const form = $(`#${modalId} form`);
        
        if (form.length && !$(this).hasClass('ft1-btn-edit')) {
            form[0].reset();
            form.find('input[type="hidden"]').val('');
            
            // Restaurar título original do modal
            const modalTitle = form.closest('.ft1-modal-content').find('.ft1-modal-header h2');
            const originalText = modalTitle.text().replace('Editar', 'Cadastrar').replace('Criar', 'Cadastrar');
            modalTitle.text(originalText);
        }
    });
});