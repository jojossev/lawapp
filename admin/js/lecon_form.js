function toggleContentFields() {
    // Récupérer tous les champs de contenu
    const contentFields = document.querySelectorAll('.content-field');
    const typeContenu = document.getElementById('type_contenu').value;
    
    // Cacher tous les champs
    contentFields.forEach(field => field.style.display = 'none');
    
    // Afficher le champ approprié selon le type
    if (typeContenu === 'texte') {
        document.getElementById('contenu-texte').style.display = 'block';
    } else if (typeContenu === 'video') {
        document.getElementById('contenu-video').style.display = 'block';
    } else if (['pdf', 'docx', 'mp3', 'mp4'].includes(typeContenu)) {
        document.getElementById('contenu-fichier').style.display = 'block';
        // Mettre à jour l'attribut accept selon le type
        const fichierInput = document.getElementById('fichier');
        if (fichierInput) {
            switch(typeContenu) {
                case 'pdf':
                    fichierInput.accept = '.pdf';
                    break;
                case 'docx':
                    fichierInput.accept = '.docx';
                    break;
                case 'mp3':
                    fichierInput.accept = '.mp3';
                    break;
                case 'mp4':
                    fichierInput.accept = '.mp4';
                    break;
            }
        }
    }
}

// Exécuter au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    toggleContentFields();
});
