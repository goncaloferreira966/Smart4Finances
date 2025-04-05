import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
  state: () => ({
    darkMode: false,
  }),
  
  actions: {
    init() {
      // Get theme preference from localStorage or use system preference as fallback
      const savedTheme = localStorage.getItem('darkMode');
      if (savedTheme !== null) {
        this.darkMode = savedTheme === 'true';
      } else {
        // Use system preference as default
        this.darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
      }
      
      // Apply theme immediately
      this.applyTheme();
      
      // Listen for system preference changes
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (localStorage.getItem('darkMode') === null) {
          this.darkMode = e.matches;
          this.applyTheme();
        }
      });
    },
    
    toggleDarkMode() {
      this.darkMode = !this.darkMode;
      localStorage.setItem('darkMode', this.darkMode.toString());
      this.applyTheme();
    },
    
    applyTheme() {
      // Apply dark mode class to the root HTML element
      if (this.darkMode) {
        document.documentElement.classList.add('dark-mode');
      } else {
        document.documentElement.classList.remove('dark-mode');
      }
    }
  }
}); 