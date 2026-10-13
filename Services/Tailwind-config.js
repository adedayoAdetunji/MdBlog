
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: '#08cf72',
        background: "#faf6f0", // warm cream
        surface: "#ffffff",
        foreground: "#2b2724", // near-black warm
        muted: "#7a7268", // warm gray
        border: "#e8e0d5",
        accent: "#e0674f", // coral
        "accent-hover": "#c9543d",
        "accent-soft": "#fbe7e1",
        'primary-dark': '#06b85f',
        'primary-light': '#0ee685',
      },
      fontFamily: {
        serif: ['"Fraunces"', "Georgia", "serif"],
        sans: ['"Inter"', "system-ui", "sans-serif"],
      },
      maxWidth: {
        prose: "68ch",
      },
      
      keyframes: {
        pop: {
          "0%": { transform: "scale(1)" },
          "40%": { transform: "scale(1.3)" },
          "100%": { transform: "scale(1)" },
        },
        "fade-up": {
          "0%": { opacity: "0", transform: "translateY(12px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
      },
      animation: {
        pop: "pop 0.35s ease-out",
        "fade-up": "fade-up 0.5s ease-out both",
      },
    },
  }
};
