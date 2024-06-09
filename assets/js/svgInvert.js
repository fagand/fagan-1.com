document.addEventListener('DOMContentLoaded', function() {
    
    function invertColor(hex) {
        // Check if the input color is null, undefined, or an empty string
        if (!hex || hex.trim() === '') {
          return '#000000'; // Return a default color, e.g., black
        }
      
        if (hex === '#000000') {
          return '#ffffff';
        } else if (hex === '#ffffff') {
          return '#000000';
        } else {
          if (hex.indexOf('#') === 0) {
            hex = hex.slice(1);
          }
          const r = parseInt(hex.slice(0, 2), 16);
          const g = parseInt(hex.slice(2, 4), 16);
          const b = parseInt(hex.slice(4, 6), 16);
      
          const newR = (255 - r).toString(16).padStart(2, '0');
          const newG = (255 - g).toString(16).padStart(2, '0');
          const newB = (255 - b).toString(16).padStart(2, '0');
      
          const newHex = `#${newR}${newG}${newB}`;
          return newHex;
        }
      }
  
      function invertSVGColors() {
        const svgs = document.querySelectorAll('.mySVG');
        const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
        svgs.forEach(svg => {
          const paths = svg.querySelectorAll('path');
    
          paths.forEach(path => {
            const fill = path.getAttribute('fill');
            const stroke = path.getAttribute('stroke');
    
            if (isDarkMode) {
              path.classList.add('dark-mode');
            } else {
              path.classList.remove('dark-mode');
            }
          });
        });
      }
    
      const isDarkModeInitially = window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (isDarkModeInitially) {
        invertSVGColors();
      }
  
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', invertSVGColors);
    invertSVGColors(); // Call the function once to handle the initial state
  });