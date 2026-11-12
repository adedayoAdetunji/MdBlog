

<style type="text/tailwindcss">
    @custom-variant dark (&:where(.dark, .dark *));

    @theme {
        --color-primary: #08cf72;
        --color-background: #faf6f0;
        --color-surface: #ffffff;
        --color-foreground: #2b2724;
        --color-muted: #7a7268;
        --color-border: #e8e0d5;
        --color-accent: #e0674f;
        --color-accent-hover: #c9543d;
        --color-accent-soft: #fbe7e1;
        --color-primary-dark: #06b85f;
        --color-primary-light: #0ee685;

        --font-serif: "Fraunces", Georgia, serif;
        --font-sans: "Inter", system-ui, sans-serif;

        --container-prose: 68ch;

        --animate-pop: pop 0.35s ease-out;
        --animate-fade-up: fade-up 0.5s ease-out both;

        @keyframes pop {
            0% { transform: scale(1); }
            40% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }

        @keyframes fade-up {
            0% {
                opacity: 0;
                transform: translateY(12px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    }
</style>