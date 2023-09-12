function levenshtein(a, b) {
    if (a.length === 0) return b.length;
    if (b.length === 0) return a.length;

    var matrix = [];

    // Increment along the first column of each row
    for (var i = 0; i <= b.length; i++) {
        matrix[i] = [i];
    }

    for (var j = 0; j <= a.length; j++) {
        matrix[0][j] = j;
    }

    // Fill in the rest of the matrix
    for (var i = 1; i <= b.length; i++) {
        for (var j = 1; j <= a.length; j++) {
            if (b.charAt(i - 1) === a.charAt(j - 1)) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(matrix[i - 1][j - 1] + 1, // Substitution
                                        Math.min(matrix[i][j - 1] + 1, // Insertion
                                                 matrix[i - 1][j] + 1)); // Deletion
            }
        }
    }

    return matrix[b.length][a.length];
}

jQuery(document).ready(function() {
    
        // Faire en sorte que tous les liens s'ouvrent dans le même onglet
        jQuery('a').attr('target', '_self');

        
                

            
// Event listener for input on the search field
jQuery("#keyword-input-adultes").on("input", function() {
    console.log("Input event triggered!");
    var input = jQuery(this).val();
    var close_keywords = [];

    // Iterate over each keyword
    jQuery.each(keywords_adultes, function(index, keyword) {
        // Calculate Levenshtein distance
        var distance = levenshtein(input, keyword);
        
        
    // Calculate dynamic threshold based on input length
    var threshold = Math.max(10 - input.length, 1);
// If distance is less than or equal to threshold, consider it a close match
        if (distance <= threshold) {
            close_keywords.push(keyword);
        }
    });

    // Update the keyword list dropdown based on close matches
    
});


// Gérer la soumission du formulaire de recherche
            jQuery("#custom-search-form-adultes").on("submit", function(event) {
                event.preventDefault();
                var keyword = jQuery("#keyword-input-adultes").val();

                if (keywords_adultes.includes(keyword)) { // Si le mot clé est dans la liste
                    if (data_adultes[keyword]) {
                        var url = data_adultes[keyword];
                        url += url.includes("?") ? "&q=" + keyword : "?q=" + keyword;
                        window.location.href = url;
                    }
                } else {
                    var errorUrl = 'https://factures.csehorizon.com/erreur/';
                    errorUrl += errorUrl.includes("?") ? "&q=" + keyword : "?q=" + keyword;
                    window.location.href = errorUrl;
                    localStorage.setItem('lastSearchedKeyword', keyword);
                }
                return false; // Empêchez l'action par défaut du formulaire
            });

            document.getElementById("keyword-input-adultes").addEventListener("focus", function() {
                document.getElementById("keywords_adultes-dropdown").style.display = "block";
            });

            document.getElementById("keywords_adultes-dropdown").addEventListener("click", function(event) {
                if (event.target === this) {
                    this.style.display = "none";
                }
            });
// Hide the dropdown when clicking outside of it
document.addEventListener("click", function(event) {
    var dropdown = document.getElementById("keywords_adultes-dropdown");
    var searchInput = document.getElementById("keyword-input-adultes");
    if (!dropdown.contains(event.target) && event.target !== searchInput) {
        dropdown.style.display = "none";
    }
});


            const keywordList = document.getElementById("keywords_adultes-dropdown");
            keywordList.addEventListener("click", function(event) {
                if (event.target.tagName.toLowerCase() === "li") {
                    const selectedKeyword = event.target.textContent;
                    document.getElementById("keyword-input-adultes").value = selectedKeyword;
                    // Preventing automatic form submission
                }
            });

            document.getElementById("keyword-input-adultes").addEventListener("click", function() {
                document.getElementById("keywords_adultes-dropdown").style.display = "block";
            });
        });

// Update the search input when clicking on a dropdown item without submitting the form
jQuery("#keywords_adultes-dropdown li").on("click", function() {
    var selectedKeyword = jQuery(this).text();
    jQuery("#keyword-input-adultes").val(selectedKeyword);
    // Not submitting the form automatically
// Real-time update of suggestions list based on user input
$("#keyword-input-adultes").on("input", function() {
    var userInput = $(this).val();

    // Perform AJAX request to get the filtered keywords
    $.get("/cse-search-adultes/v1/keywords_adultes", { input: userInput }, function(data_adultes) {
        // Clear the suggestions list
        $("#keywords-list-adultes").empty();

        // Populate the suggestions list with the received keywords
        $.each(data_adultes, function(keyword, url) {
            var option = document.createElement("option");
            option.value = keyword;
            $("#keywords-list-adultes").append("<li>" + keyword + "</li>");
        });
    });
});
});