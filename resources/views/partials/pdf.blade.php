@push('scripts')
    <script src="{{ asset('js/vendor/html2pdf.bundle.min.js') }}"></script>
    <script>
        window.downloadPdf = function (elementId, filename) {
            const element = document.getElementById(elementId);

            if (!element || typeof html2pdf === 'undefined') {
                window.print();
                return;
            }

            // Force light tokens while capturing; PDFs must never be dark.
            element.classList.add('theme-light');
            const restoreTheme = () => element.classList.remove('theme-light');

            html2pdf()
                .set({
                    margin: 10,
                    filename: filename,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                    pagebreak: { mode: ['css', 'legacy'] },
                })
                .from(element)
                .save()
                .then(restoreTheme, restoreTheme);
        };
    </script>
@endpush
