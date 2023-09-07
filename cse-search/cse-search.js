jQuery(document).ready(function ($) {
    // Faire en sorte que tous les liens s'ouvrent dans le même onglet
    $('a').attr('target', '_self');

    // Obtenir les mots clés de l'API REST de WordPress
    $.get(CSESearch.rest_url, function (data) {
        var dataList = $("#keywords");
        var keywords = []; // Créez un tableau pour stocker les mots clés
        $.each(data, function (keyword, url) {
            var option = $("<option>").val(keyword).text(keyword);
            dataList.append(option);
            keywords.push(keyword); // Ajoutez le mot clé au tableau
        });

        // Gérer la soumission du formulaire de recherche
        $("#custom-search-form").on("submit", function (event) {
            event.preventDefault();
            var keyword = $("#keyword-input").val();
            var url = data[keyword]; // Récupérer l'URL à partir du tableau de données
            if (keywords.includes(keyword)) { // Si le mot clé est dans la liste
                if (url) {
                    url += url.includes("?") ? "&q=" + keyword : "?q=" + keyword; // Ajoutez le mot clé à l'URL
                    window.location.href = url; // Change la location actuelle à l'URL récupérée
                }
            // ...
			} else {
				// Si aucune correspondance n'est trouvée, redirigez vers la page d'erreur
				var errorUrl = 'https://factures.csehorizon.com/erreur/';
				var keyword = $("#keyword-input").val();
				errorUrl += errorUrl.includes("?") ? "&q=" + keyword : "?q=" + keyword; // Ajoutez le mot clé à l'URL d'erreur
				window.location.href = errorUrl;
			
				// Enregistrez le mot clé dans le stockage local
				localStorage.setItem('lastSearchedKeyword', keyword);
			}
			// ...
            return false; // Empêchez l'action par défaut du formulaire
        });
    });

    // Ajouter le nonce au formulaire pour la sécurité
    var nonce = $('<input>').attr({
        type: 'hidden',
        id: 'nonce',
        name: 'nonce',
        value: CSESearch.nonce
    });
    $("#custom-search-form").append(nonce);
});