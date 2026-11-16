<script>
    /*
     | Apply the saved (or OS) theme before first paint so dark mode never
     | flashes light. The Alpine store below drives the header toggle.
     */
    (function () {
        try {
            var stored = localStorage.getItem('vermex-theme');
            var dark = stored
                ? stored === 'dark'
                : window.matchMedia('(prefers-color-scheme: dark)').matches;

            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        } catch (error) {
            /* localStorage can be unavailable in hardened browsers. */
        }
    })();

    document.addEventListener('alpine:init', function () {
        Alpine.store('theme', {
            dark: document.documentElement.classList.contains('dark'),

            toggle: function () {
                this.dark = !this.dark;
                document.documentElement.classList.toggle('dark', this.dark);
                document.documentElement.style.colorScheme = this.dark ? 'dark' : 'light';

                try {
                    localStorage.setItem('vermex-theme', this.dark ? 'dark' : 'light');
                } catch (error) {
                    /* Ignore storage failures; the current page stays themed. */
                }
            },
        });

        // Keep following the OS only while the user has not made a choice.
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (event) {
            var stored = null;

            try {
                stored = localStorage.getItem('vermex-theme');
            } catch (error) {
                /* Ignore */
            }

            if (stored) {
                return;
            }

            Alpine.store('theme').dark = event.matches;
            document.documentElement.classList.toggle('dark', event.matches);
            document.documentElement.style.colorScheme = event.matches ? 'dark' : 'light';
        });
    });
</script>
