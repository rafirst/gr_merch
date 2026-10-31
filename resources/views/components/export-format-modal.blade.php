<div class="export-format-modal" id="{{ $modalId }}" aria-hidden="true">
    <div class="export-format-backdrop" data-export-format-close></div>
    <section class="export-format-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}Title">
        <div class="export-format-dialog-header">
            <div>
                <span class="export-format-kicker">{{ $label }}</span>
                <h3 id="{{ $modalId }}Title">Pilih Format Ekspor</h3>
            </div>
            <button type="button" class="export-format-close" data-export-format-close aria-label="Tutup pilihan ekspor">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
        <div class="export-format-options">
            <a href="{{ $pdfUrl }}" class="export-format-option export-format-option-pdf">
                <span class="export-format-option-icon"><i class="fas fa-file-pdf" aria-hidden="true"></i></span>
                <span class="export-format-option-copy"><strong>Export PDF</strong><small>Unduh laporan dalam format PDF</small></span>
                <i class="fas fa-chevron-right export-format-option-arrow" aria-hidden="true"></i>
            </a>
            @isset($excelUrl)
                <a href="{{ $excelUrl }}" class="export-format-option export-format-option-excel">
                    <span class="export-format-option-icon"><i class="fas fa-file-excel" aria-hidden="true"></i></span>
                    <span class="export-format-option-copy"><strong>Export Excel</strong><small>Unduh data dalam format XLSX</small></span>
                    <i class="fas fa-chevron-right export-format-option-arrow" aria-hidden="true"></i>
                </a>
            @endisset
        </div>
        <button type="button" class="export-format-cancel" data-export-format-close>Batal</button>
    </section>
</div>

@push('styles')
<style>
    .export-format-modal {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: grid;
        visibility: hidden;
        place-items: center;
        opacity: 0;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }

    .export-format-modal.is-visible {
        visibility: visible;
        opacity: 1;
    }

    .export-format-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 4, 9, 0.78);
        backdrop-filter: blur(4px);
    }

    .export-format-dialog {
        position: relative;
        width: min(430px, calc(100% - 2rem));
        padding: 1.25rem;
        border: 1px solid rgba(105, 143, 176, 0.4);
        border-top: 3px solid #df071a;
        border-radius: 6px;
        background: linear-gradient(145deg, #172532, #050c14 72%);
        box-shadow: 0 20px 55px rgba(0, 0, 0, 0.6);
        transform: translateY(12px);
        transition: transform 0.2s ease;
    }

    .export-format-modal.is-visible .export-format-dialog {
        transform: translateY(0);
    }

    .export-format-dialog-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .export-format-kicker {
        color: #ff6570;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .export-format-dialog h3 {
        margin: 0.25rem 0 0;
        color: #ffffff;
        font-size: 1.15rem;
    }

    .export-format-close {
        display: inline-grid;
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.06);
        color: rgba(244, 247, 250, 0.8);
        cursor: pointer;
    }

    .export-format-options {
        display: grid;
        gap: 0.6rem;
    }

    .export-format-option {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
        padding: 0.75rem;
        border: 1px solid rgba(105, 143, 176, 0.28);
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.035);
        color: #ffffff;
        text-decoration: none;
        transition: border-color 0.16s ease, background-color 0.16s ease;
    }

    .export-format-option:hover,
    .export-format-option:focus {
        border-color: rgba(255, 255, 255, 0.45);
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        text-decoration: none;
    }

    .export-format-option-icon {
        display: inline-grid;
        width: 38px;
        height: 38px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 4px;
        background: rgba(223, 7, 26, 0.16);
        color: #ff5a68;
        font-size: 1.1rem;
    }

    .export-format-option-excel .export-format-option-icon {
        background: rgba(25, 174, 83, 0.16);
        color: #5be58a;
    }

    .export-format-option-copy {
        display: flex;
        min-width: 0;
        flex: 1;
        flex-direction: column;
        gap: 0.18rem;
    }

    .export-format-option-copy strong {
        font-size: 0.9rem;
    }

    .export-format-option-copy small {
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.74rem;
    }

    .export-format-option-arrow {
        color: rgba(244, 247, 250, 0.45);
        font-size: 0.75rem;
    }

    .export-format-cancel {
        display: block;
        min-height: 36px;
        margin: 0.9rem 0 0 auto;
        padding: 0.4rem 0.8rem;
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 4px;
        background: transparent;
        color: rgba(244, 247, 250, 0.78);
        cursor: pointer;
        font-size: 0.8rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.export-format-modal').forEach(function (modal) {
            const openButton = document.querySelector('[aria-controls="' + modal.id + '"]');
            const pdfLink = modal.querySelector('.export-format-option-pdf');
            let previouslyFocusedElement = null;

            function closeModal() {
                modal.classList.remove('is-visible');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                previouslyFocusedElement?.focus();
            }

            openButton.addEventListener('click', function () {
                previouslyFocusedElement = document.activeElement;
                modal.classList.add('is-visible');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                pdfLink.focus();
            });

            modal.querySelectorAll('[data-export-format-close]').forEach(function (element) {
                element.addEventListener('click', closeModal);
            });

            modal.querySelectorAll('.export-format-option').forEach(function (link) {
                link.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-visible')) {
                    closeModal();
                }
            });
        });
    });
</script>
@endpush
