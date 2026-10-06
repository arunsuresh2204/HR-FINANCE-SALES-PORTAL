import './bootstrap';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

document.addEventListener('alpine:init', () => {
    Alpine.data('richEditor', (model, initial, placeholder) => ({
        quill: null,

        init() {
            this.quill = new Quill(this.$refs.editor, {
                theme: 'snow',
                placeholder: placeholder || '',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ header: [1, 2, 3, false] }],
                        ['blockquote', 'code-block'],
                        ['link'],
                        ['clean'],
                    ],
                },
            });

            if (initial) {
                this.quill.root.innerHTML = initial;
            }

            let debounce;
            this.quill.on('text-change', () => {
                clearTimeout(debounce);
                debounce = setTimeout(() => {
                    const empty = this.quill.getText().trim().length === 0;
                    this.$wire.set(model, empty ? '' : this.quill.root.innerHTML);
                }, 300);
            });
        },
    }));
});
