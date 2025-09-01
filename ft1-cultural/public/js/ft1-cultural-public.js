jQuery(document).ready(function($) {
    'use strict';

    // Inicializar componentes
    initTabs();
    initSignature();
    initForms();

    // Sistema de abas
    function initTabs() {
        $('.ft1-nav-tab a').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this).attr('href');
            
            // Remover classe ativa de todas as abas
            $('.ft1-nav-tab').removeClass('active');
            $('.ft1-tab-content').removeClass('active');
            
            // Adicionar classe ativa na aba clicada
            $(this).parent().addClass('active');
            $(target).addClass('active');
        });
    }

    // Sistema de assinatura digital
    function initSignature() {
        $('.ft1-signature-btn').on('click', function() {
            const contractId = $(this).data('contract-id');
            showSignatureModal(contractId);
        });

        $('.ft1-sign-contract').on('click', function() {
            const contractId = $(this).data('contract-id');
            signContract(contractId);
        });
    }

    function showSignatureModal(contractId) {
        const signature = prompt('Para assinar este contrato, digite seu nome completo:');
        
        if (signature && signature.trim().length > 0) {
            signContract(contractId, signature.trim());
        }
    }

    function signContract(contractId, signature) {
        const data = {
            action: 'ft1_sign_contract',
            contract_id: contractId,
            signature: signature,
            nonce: ft1_public_ajax.nonce
        };

        showLoading();

        $.post(ft1_public_ajax.ajax_url, data)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', response.data.message);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('error', response.data.message);
                }
            })
            .fail(function() {
                hideLoading();
                showAlert('error', 'Erro de conexão. Tente novamente.');
            });
    }

    // Formulários públicos
    function initForms() {
        $('.ft1-public-form').on('submit', function(e) {
            e.preventDefault();
            submitPublicForm($(this));
        });

        // Máscaras de input
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
    }

    function submitPublicForm(form) {
        const formData = form.serialize() + '&nonce=' + ft1_public_ajax.nonce;
        const action = form.data('action');

        showLoading();

        $.post(ft1_public_ajax.ajax_url, formData + '&action=' + action)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showAlert('success', response.data.message);
                    form[0].reset();
                } else {
                    showAlert('error', response.data.message);
                }
            })
            .fail(function() {
                hideLoading();
                showAlert('error', 'Erro de conexão. Tente novamente.');
            });
    }

    // Filtros e busca
    $('.ft1-search-input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const targetCards = $(this).data('target');
        
        $(targetCards).each(function() {
            const cardText = $(this).text().toLowerCase();
            if (cardText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Funções utilitárias
    function showLoading() {
        if ($('#ft1-public-loading').length === 0) {
            $('body').append('<div id="ft1-public-loading" class="ft1-loading"><div class="spinner"></div></div>');
        }
        $('#ft1-public-loading').show();
    }

    function hideLoading() {
        $('#ft1-public-loading').hide();
    }

    function showAlert(type, message) {
        const alert = $(`<div class="ft1-alert ${type}">${message}</div>`);
        
        // Remover alertas existentes
        $('.ft1-alert').remove();
        
        // Adicionar novo alerta no topo do conteúdo
        $('.ft1-cultural-public').prepend(alert);
        
        // Scroll para o topo
        $('html, body').animate({ scrollTop: 0 }, 300);
        
        // Auto-remover após 5 segundos
        setTimeout(function() {
            alert.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Animações de entrada
    function animateOnScroll() {
        $('.ft1-edital-card, .ft1-stat-card, .ft1-contract-item').each(function() {
            const elementTop = $(this).offset().top;
            const elementBottom = elementTop + $(this).outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();

            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).addClass('animated');
            }
        });
    }

    // Verificar animações no scroll
    $(window).on('scroll', animateOnScroll);
    animateOnScroll(); // Executar uma vez no carregamento

    // Smooth scroll para links internos
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 600);
        }
    });

    // Validação de formulários em tempo real
    $('.ft1-form input[required], .ft1-form textarea[required]').on('blur', function() {
        validateField($(this));
    });

    function validateField(field) {
        const value = field.val().trim();
        const type = field.attr('type');
        
        field.removeClass('error');
        field.next('.error-message').remove();

        if (value === '') {
            showFieldError(field, 'Este campo é obrigatório');
            return false;
        }

        if (type === 'email' && !isValidEmail(value)) {
            showFieldError(field, 'Digite um e-mail válido');
            return false;
        }

        return true;
    }

    function showFieldError(field, message) {
        field.addClass('error');
        field.after(`<span class="error-message" style="color: red; font-size: 12px; margin-top: 5px; display: block;">${message}</span>`);
    }

    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    // Tooltips informativos
    $('.ft1-tooltip').on('mouseenter', function() {
        const tooltip = $(this).data('tooltip');
        $(this).append(`<div class="tooltip-content">${tooltip}</div>`);
    }).on('mouseleave', function() {
        $('.tooltip-content').remove();
    });

    // Auto-save para formulários longos
    $('.ft1-auto-save').on('input', function() {
        const form = $(this).closest('form');
        const formId = form.attr('id');
        
        if (formId) {
            clearTimeout(window.autoSaveTimer);
            window.autoSaveTimer = setTimeout(() => {
                saveFormData(form, formId);
            }, 2000);
        }
    });

    function saveFormData(form, formId) {
        const formData = form.serialize();
        localStorage.setItem(`ft1_form_${formId}`, formData);
        
        // Mostrar indicador de salvamento
        showAutoSaveIndicator();
    }

    function loadFormData(form, formId) {
        const savedData = localStorage.getItem(`ft1_form_${formId}`);
        if (savedData) {
            const fields = savedData.split('&');
            fields.forEach(field => {
                const [name, value] = field.split('=');
                form.find(`[name="${name}"]`).val(decodeURIComponent(value));
            });
        }
    }

    function showAutoSaveIndicator() {
        if ($('#auto-save-indicator').length === 0) {
            $('body').append('<div id="auto-save-indicator" style="position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 10px; border-radius: 4px; font-size: 12px; z-index: 9999;">💾 Salvo automaticamente</div>');
        }
        
        $('#auto-save-indicator').fadeIn(300);
        
        setTimeout(() => {
            $('#auto-save-indicator').fadeOut(300);
        }, 2000);
    }

    // Carregar dados salvos nos formulários
    $('.ft1-auto-save form').each(function() {
        const formId = $(this).attr('id');
        if (formId) {
            loadFormData($(this), formId);
        }
    });
});