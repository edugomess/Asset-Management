$(document).ready(function () {
    let debounceTimer;

    // Check if the element exists to avoid errors on pages without it (though we plan to add it)
    if ($('#globalSearchInput').length === 0) return;

    $('#globalSearchInput').on('input', function () {
        clearTimeout(debounceTimer);
        let query = $(this).val();
        let resultBox = $('#globalSearchResults');

        if (query.length < 2) {
            resultBox.hide();
            return;
        }

        debounceTimer = setTimeout(function () {
            $.ajax({
                url: '/search_backend.php', // Use absolute path to ensure custom routing works from subdirs if any
                method: 'GET',
                data: { q: query },
                dataType: 'json',
                success: function (data) {
                    resultBox.empty();
                    if (data.length > 0) {
                        data.forEach(function (item) {
                            let icon = 'fa-search';
                            if (item.type === 'user') icon = 'fa-user';
                            else if (item.type === 'asset') icon = 'fa-box';
                            else if (item.type === 'ticket') icon = 'fa-headset';
                            else if (item.type === 'cost_center') icon = 'fa-building';
                            else if (item.type === 'supplier') icon = 'fa-truck';
                            else if (item.type === 'license') icon = 'fa-key';

                            resultBox.append(`
                                <a class="dropdown-item d-flex align-items-center" href="${item.url}">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas ${icon} text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">${item.category}</div>
                                        <span class="font-weight-bold">${item.label}</span>
                                    </div>
                                </a>
                            `);
                        });
                        resultBox.show();
                    } else {
                        resultBox.append('<a class="dropdown-item text-center small text-gray-500" href="#">Nenhum resultado encontrado</a>');
                        resultBox.show();
                    }
                },
                error: function () {
                    console.error("Erro na busca global");
                }
            });
        }, 300);
    });

    // Hide results when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.navbar-search').length) {
            $('#globalSearchResults').hide();
        }
    });
});
