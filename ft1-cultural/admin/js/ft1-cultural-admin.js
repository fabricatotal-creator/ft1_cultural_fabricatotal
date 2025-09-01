jQuery(document).ready(function($) {
    'use strict';

    // Variáveis globais
    let currentModal = null;

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
            $('body').removeClass('modal-open');
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
        // Implementar edição conforme o tipo
        console.log(`Editando ${type} com ID ${id}`);
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

        $('input[type="file"]').on('change', function() {
            const input = this;
            const proponentId = $(input).data('proponent-id');
            
            if (input.files && input.files[0]) {
                uploadDocument(input.files[0], proponentId);
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
                    location.reload();
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

    // Funções utilitárias
    function showLoading() {
        if ($('#ft1-loading').length === 0) {
            $('body').append('<div id="ft1-loading" class="ft1-loading"><div class="spinner"></div></div>');
        }
        $('#ft1-loading').show();
    }

    function hideLoading() {
        $('#ft1-loading').hide();
    }

    function showAlert(type, message) {
        const alert = $(`<div class="ft1-alert ${type}">${message}</div>`);
        
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
    if (typeof $ !== 'undefined') {
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
            value = (value / 100).toFixed(2) + '';
            value = value.replace(".", ",");
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            this.value = 'R$ ' + value;
        });
    }
});