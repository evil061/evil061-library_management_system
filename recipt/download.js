            function downloadCertificate(certificateContainer, name) {
              if (name.trim() === '') {
                alert('Please enter a name for the recipient.');
                return;
              }

              const certificateCanvas = document.createElement('canvas');
              const context = certificateCanvas.getContext('2d');

              // Set canvas dimensions to match the certificate container
              certificateCanvas.width = certificateContainer.offsetWidth;
              certificateCanvas.height = certificateContainer.offsetHeight;

              // Draw the certificate content onto the canvas using html2canvas library (assumed to be included)
              html2canvas(certificateContainer, { canvas: certificateCanvas }).then(canvas => {
                const imageURL = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.href = imageURL;
                link.download = `Certificate_for_${name}.png`;
                link.click();
              });
            }
