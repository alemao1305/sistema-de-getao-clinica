/**
 * CEP Mask & Auto-fill Address Utility
 * Suporta ViaCEP, BrasilAPI e rota interna /consulta-cep/{cep}
 * Clínica Fabris
 */

export function formatCep(value) {
    if (!value) return '';
    const digits = String(value).replace(/\D/g, '').slice(0, 8);
    if (digits.length > 5) {
        return `${digits.slice(0, 5)}-${digits.slice(5)}`;
    }
    return digits;
}

export async function fetchCepData(cleanCep) {
    // 1. Consulta direta no ViaCEP (CORS habilitado por padrão)
    try {
        const response = await fetch(`https://viacep.com.br/ws/${cleanCep}/json/`, {
            headers: { 'Accept': 'application/json' }
        });
        if (response.ok) {
            const data = await response.json();
            if (!data.erro) {
                return {
                    logradouro: data.logradouro || '',
                    bairro: data.bairro || '',
                    cidade: data.localidade || '',
                    estado: data.uf || '',
                    complemento: data.complemento || '',
                };
            } else {
                throw new Error('CEP_NOT_FOUND');
            }
        }
    } catch (err) {
        if (err.message === 'CEP_NOT_FOUND') {
            throw err;
        }
        // Falha de rede ou CORS -> tentar BrasilAPI
    }

    // 2. Fallback: BrasilAPI
    try {
        const response = await fetch(`https://brasilapi.com.br/api/cep/v1/${cleanCep}`);
        if (response.ok) {
            const data = await response.json();
            return {
                logradouro: data.street || '',
                bairro: data.neighborhood || '',
                cidade: data.city || '',
                estado: data.state || '',
                complemento: '',
            };
        } else if (response.status === 404) {
            throw new Error('CEP_NOT_FOUND');
        }
    } catch (err) {
        if (err.message === 'CEP_NOT_FOUND') {
            throw err;
        }
        // Falha de rede -> tentar rota do backend Laravel
    }

    // 3. Fallback: Rota interna Laravel
    try {
        const response = await fetch(`/consulta-cep/${cleanCep}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (response.ok) {
            const data = await response.json();
            return {
                logradouro: data.logradouro || '',
                bairro: data.bairro || '',
                cidade: data.cidade || '',
                estado: data.estado || '',
                complemento: data.complemento || '',
            };
        } else if (response.status === 404) {
            throw new Error('CEP_NOT_FOUND');
        }
    } catch (err) {
        if (err.message === 'CEP_NOT_FOUND') {
            throw err;
        }
    }

    throw new Error('NETWORK_ERROR');
}

function dispatchChange(el) {
    if (!el) return;
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
}

export function isCepInput(el) {
    if (!el || el.tagName !== 'INPUT') return false;
    const name = (el.name || '').toLowerCase();
    const id = (el.id || '').toLowerCase();
    const placeholder = (el.placeholder || '').toLowerCase();
    const dataMask = (el.dataset.mask || '').toLowerCase();

    return id === 'cep' || name === 'cep' || dataMask === 'cep' || placeholder.includes('00000');
}

export function findAddressFields(cepInput) {
    const container = cepInput.closest('form') || cepInput.closest('.grid') || cepInput.closest('.space-y-4') || document;
    
    return {
        logradouro: container.querySelector('#logradouro, [name="logradouro"], [name="rua"], #rua, [name="endereco"], #endereco'),
        bairro: container.querySelector('#bairro, [name="bairro"]'),
        cidade: container.querySelector('#cidade, [name="cidade"], [name="municipio"], #municipio'),
        estado: container.querySelector('#estado, [name="estado"], [name="uf"], #uf'),
        numero: container.querySelector('#numero, [name="numero"]'),
        loading: container.querySelector('#cep-loading') || cepInput.parentElement?.querySelector('#cep-loading'),
        feedback: container.querySelector('#cep-feedback') || cepInput.parentElement?.parentElement?.querySelector('#cep-feedback'),
    };
}

export function setupCepInput(cepInput) {
    if (!cepInput || cepInput._cepInitialized) return;
    cepInput._cepInitialized = true;

    cepInput.setAttribute('maxlength', '9');
    if (!cepInput.getAttribute('placeholder')) {
        cepInput.setAttribute('placeholder', '00000-000');
    }
    cepInput.setAttribute('autocomplete', 'postal-code');

    // Formata valor inicial se existente
    if (cepInput.value) {
        cepInput.value = formatCep(cepInput.value);
    }

    let lastSearchedCep = cepInput.value.replace(/\D/g, '');
    let isFetching = false;

    const fields = findAddressFields(cepInput);
    let feedbackEl = fields.feedback;
    let loadingEl = fields.loading;

    if (!feedbackEl) {
        feedbackEl = document.createElement('span');
        feedbackEl.className = 'text-[11px] text-slate-500 mt-1 block min-h-[16px] transition-colors';
        const targetParent = cepInput.parentElement?.classList.contains('relative')
            ? cepInput.parentElement
            : cepInput;
        targetParent.insertAdjacentElement('afterend', feedbackEl);
    }

    function setLoading(loading) {
        if (loadingEl) {
            loadingEl.classList.toggle('hidden', !loading);
        }
    }

    function setFeedback(msg, type = 'info') {
        if (!feedbackEl) return;
        feedbackEl.textContent = msg || '';
        feedbackEl.className = 'text-[11px] mt-1 block min-h-[16px] transition-colors ' + (
            type === 'success' ? 'text-emerald-600 font-semibold' :
            type === 'error' ? 'text-rose-600 font-semibold' :
            type === 'warning' ? 'text-amber-600 font-semibold' :
            'text-slate-500'
        );
        feedbackEl.dataset.type = type;
    }

    function clearFeedback() {
        if (!feedbackEl) return;
        feedbackEl.textContent = '';
        feedbackEl.className = 'text-[11px] text-slate-500 mt-1 block min-h-[16px] transition-colors';
        delete feedbackEl.dataset.type;
    }

    async function executeSearch(cleanDigits) {
        if (isFetching || cleanDigits.length !== 8) return;
        if (cleanDigits === lastSearchedCep && feedbackEl && feedbackEl.dataset.type === 'success') {
            return;
        }

        isFetching = true;
        setLoading(true);
        setFeedback('Buscando endereço...', 'info');

        try {
            const data = await fetchCepData(cleanDigits);
            lastSearchedCep = cleanDigits;

            // Busca os campos novamente caso o DOM tenha mudado
            const currentFields = findAddressFields(cepInput);

            if (currentFields.logradouro && data.logradouro) {
                currentFields.logradouro.value = data.logradouro;
                dispatchChange(currentFields.logradouro);
            }
            if (currentFields.bairro && data.bairro) {
                currentFields.bairro.value = data.bairro;
                dispatchChange(currentFields.bairro);
            }
            if (currentFields.cidade && data.cidade) {
                currentFields.cidade.value = data.cidade;
                dispatchChange(currentFields.cidade);
            }
            if (currentFields.estado && data.estado) {
                currentFields.estado.value = data.estado.toUpperCase();
                dispatchChange(currentFields.estado);
            }

            setFeedback('Endereço preenchido automaticamente!', 'success');

            if (currentFields.numero) {
                currentFields.numero.focus();
                if (currentFields.numero.select) currentFields.numero.select();
            }

            setTimeout(() => {
                if (feedbackEl && feedbackEl.dataset.type === 'success') {
                    clearFeedback();
                }
            }, 3500);

        } catch (error) {
            if (error.message === 'CEP_NOT_FOUND') {
                setFeedback('CEP não encontrado. Verifique o número digitado.', 'error');
            } else {
                setFeedback('Não foi possível buscar o CEP automaticamente. Preencha manualmente.', 'warning');
            }
        } finally {
            isFetching = false;
            setLoading(false);
        }
    }

    cepInput.addEventListener('input', () => {
        const clean = cepInput.value.replace(/\D/g, '').slice(0, 8);
        const formatted = formatCep(clean);

        if (cepInput.value !== formatted) {
            cepInput.value = formatted;
        }

        if (clean.length < 8) {
            if (feedbackEl && (feedbackEl.dataset.type === 'error' || feedbackEl.dataset.type === 'warning')) {
                clearFeedback();
            }
            lastSearchedCep = '';
        } else if (clean.length === 8 && clean !== lastSearchedCep) {
            executeSearch(clean);
        }
    });

    cepInput.addEventListener('blur', () => {
        const clean = cepInput.value.replace(/\D/g, '');
        if (clean.length === 8 && clean !== lastSearchedCep) {
            executeSearch(clean);
        } else if (clean.length > 0 && clean.length < 8) {
            setFeedback('CEP incompleto (deve conter 8 dígitos).', 'warning');
        }
    });
}

export function initCepAutoFill() {
    const inputs = document.querySelectorAll('input#cep, input[name="cep"], input[data-mask="cep"]');
    inputs.forEach(setupCepInput);
}

// Inicialização imediata com delegação global e MutationObserver
if (typeof window !== 'undefined') {
    // Delegação global de eventos (captura digitação em qualquer input de CEP mesmo que inserido dinamicamente)
    document.addEventListener('input', (e) => {
        if (isCepInput(e.target)) {
            if (!e.target._cepInitialized) {
                setupCepInput(e.target);
            }
        }
    }, true);

    document.addEventListener('focusin', (e) => {
        if (isCepInput(e.target)) {
            if (!e.target._cepInitialized) {
                setupCepInput(e.target);
            }
        }
    }, true);

    // Observa mudanças no DOM (troca de abas, modais abrindo, elementos inseridos)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(() => {
            initCepAutoFill();
        });
        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true });
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                observer.observe(document.body, { childList: true, subtree: true });
            });
        }
    }
}
