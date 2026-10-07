/**
 * dist/js/modulos/ayuda/ayuda.js
 * ---------------------------------------------------------------
 * Lógica interactiva para el Centro de Ayuda de DIRPOLES-4.
 */

document.addEventListener("DOMContentLoaded", () => {
    const helpSearch = document.getElementById("helpSearch");
    const helpTopicsContainer = document.getElementById("helpTopics");
    const filterChips = document.querySelectorAll(".help-filter-chip");
    const modalElement = document.getElementById("modalGuia");
    const guiaTitle = document.getElementById("guiaTitle");
    const guiaCategory = document.getElementById("guiaCategory");
    const guiaContent = document.getElementById("guiaContent");
    const btnIrModulo = document.getElementById("btnIrModulo");
    const btnDescargarManual = document.getElementById("btnDescargarManual");

    let helpModal = null;
    if (modalElement && typeof bootstrap !== "undefined") {
        helpModal = new bootstrap.Modal(modalElement);
    }

    let guidesData = window.HELP_GUIDES_LOCAL || { categorias: {}, guias: {} };

    // ─── 1. Cargar datos vía API o Fallback ───
    async function cargarDatosAyuda() {
        try {
            if (typeof apiFetch === "function") {
                const res = await apiFetch(BASE_URL + 'api/ayuda/guias');
                if (res && res.categorias && res.guias) {
                    guidesData = res;
                }
            }
        } catch (e) {
            console.info("Cargando catálogo local de Ayuda...");
        }
        renderizarCards();
    }

    // ─── 2. Renderizar Tarjetas de Categorías ───
    function renderizarCards() {
        if (!helpTopicsContainer) return;

        let html = "";
        const categorias = guidesData.categorias || {};
        const guias = guidesData.guias || {};

        Object.keys(categorias).forEach(catKey => {
            const cat = categorias[catKey];
            const catGuiasKeys = Object.keys(guias).filter(gKey => guias[gKey].categoria_key === catKey);

            if (catGuiasKeys.length === 0) return;

            let faqItemsHtml = "";
            catGuiasKeys.forEach(gKey => {
                const g = guias[gKey];
                faqItemsHtml += `
                    <li class="faq-item open-guide" data-guide="${gKey}">
                        <span>${g.title}</span>
                        <i class="fa-solid fa-chevron-right text-muted"></i>
                    </li>`;
            });

            html += `
                <div class="col-xl-4 col-md-6 mb-4 help-topic-item" 
                     data-group="${cat.grupo || 'general'}"
                     data-keywords="${cat.keywords || ''}">
                    <div class="card topic-card">
                        <div class="card-body">
                            <div class="topic-icon-container bg-soft-${cat.color || 'primary'}">
                                <i class="fa-solid ${cat.icono}"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">${cat.titulo}</h5>
                            <p class="text-muted small mb-3">${cat.descripcion}</p>
                            <ul class="faq-list">
                                ${faqItemsHtml}
                            </ul>
                        </div>
                    </div>
                </div>`;
        });

        helpTopicsContainer.innerHTML = html;
        conectarEventosGuia();
    }

    // ─── 3. Buscador Inteligente en Tiempo Real ───
    if (helpSearch) {
        helpSearch.addEventListener("input", function (e) {
            const term = e.target.value.toLowerCase().trim();
            const topicItems = document.querySelectorAll(".help-topic-item");

            topicItems.forEach(card => {
                const keywords = card.getAttribute("data-keywords").toLowerCase();
                const text = card.innerText.toLowerCase();

                if (term === "" || keywords.includes(term) || text.includes(term)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        });
    }

    // ─── 4. Filtros por Chips (Categorías/Roles) ───
    filterChips.forEach(chip => {
        chip.addEventListener("click", function () {
            filterChips.forEach(c => c.classList.remove("active"));
            this.classList.add("active");

            const filter = this.getAttribute("data-filter");
            const topicItems = document.querySelectorAll(".help-topic-item");

            topicItems.forEach(card => {
                const group = card.getAttribute("data-group");
                if (filter === "all" || group === filter) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });

            if (helpSearch) helpSearch.value = "";
        });
    });

    // ─── 5. Renderizar Contenido de Guía en Modal ───
    function renderGuide(guideKey) {
        const guias = guidesData.guias || {};
        const guide = guias[guideKey];

        if (!guide) {
            guiaTitle.innerText = "Guía no disponible";
            guiaCategory.innerText = "Centro de Ayuda";
            guiaContent.innerHTML = `
                <div class="guide-empty">
                    <i class="fa-solid fa-screwdriver-wrench fa-3x mb-3 text-muted"></i>
                    <h5 class="fw-bold">Contenido en actualización</h5>
                    <p class="text-muted">Esta sección se está actualizando para reflejar la última versión del sistema.</p>
                </div>`;
            if (btnIrModulo) btnIrModulo.style.display = "none";
            return;
        }

        guiaTitle.innerText = guide.title;
        guiaCategory.innerText = guide.category;

        let stepsHTML = '<div class="guide-steps">';
        guide.steps.forEach((step, index) => {
            stepsHTML += `
                <div class="guide-step">
                    <div class="guide-step-number">${index + 1}</div>
                    <div class="guide-step-icon">
                        <i class="fa-solid ${step.icon}"></i>
                    </div>
                    <div class="guide-step-text">${step.text}</div>
                </div>`;
        });
        stepsHTML += '</div>';

        let tipHTML = '';
        if (guide.tip) {
            tipHTML = `
                <div class="guide-tip">
                    <i class="fa-solid fa-lightbulb mt-1"></i>
                    <div><b>Consejo:</b> ${guide.tip}</div>
                </div>`;
        }

        guiaContent.innerHTML = stepsHTML + tipHTML;

        if (btnIrModulo) {
            if (guide.route) {
                btnIrModulo.style.display = "inline-block";
                btnIrModulo.onclick = () => {
                    window.location.href = BASE_URL + guide.route;
                };
            } else {
                btnIrModulo.style.display = "none";
            }
        }
    }

    function conectarEventosGuia() {
        document.querySelectorAll(".open-guide").forEach(item => {
            item.addEventListener("click", function () {
                const guideKey = this.getAttribute("data-guide");
                renderGuide(guideKey);
                if (helpModal) helpModal.show();
            });
        });
    }

    // ─── 6. Botón Descargar Manual de Usuario (Hero Header) ───
    if (btnDescargarManual) {
        btnDescargarManual.addEventListener("click", () => {
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    icon: 'info',
                    title: 'Manual de Usuario DIRPOLES 4',
                    html: 'El manual oficial ilustrado en formato PDF se encuentra en fase final de redacción y estará disponible para su descarga en la próxima versión del sistema.',
                    confirmButtonColor: '#4e73df',
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert("El manual oficial en formato PDF se encuentra en preparación.");
            }
        });
    }

    // Inicializar
    cargarDatosAyuda();
});
