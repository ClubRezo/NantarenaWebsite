(function ($) {

    function init($table) {
        // Génération des filtres
        $table.addClass('tab-content');
        $("tbody:not(.empty)", $table).addClass("raw-body").addClass("hide");

        $table.on('click', "tbody:not(.raw-body, .empty) tr input[type='checkbox']", function() {
            var id = $(this).attr('id');
            $("tbody.raw-body #"+id, $table).prop('checked', $(this).prop('checked'));
        });

        var filters = $('<tr></tr>');

        $('th', $table).each(function(index) {
            var filter = $('<th></th>');

            if ($(this).hasClass('tiny'))   filter.addClass('tiny');
            if ($(this).hasClass('mini'))   filter.addClass('mini');

            if ($(this).text()) {

                if ($(this).data('filter') && $(this).data('filter') == 'select') {

                    filter.append('<select class="filter filter-select" data-field="'+ index +'"></select>');
                    var uniq = Array();
                    $("tbody:not(.empty) tr td:nth-child("+(index+1)+")", $table).each(function() {
                        if ($.inArray($(this).text(), uniq) == -1)
                            uniq.push($(this).text());
                    });
                    uniq.sort();
                    $("select", filter).append('<option value="">--</option>');
                    uniq.forEach(function(e) {
                        $("select", filter).append('<option value="'+ e +'">'+ e +'</option>');
                    });

                    filter.change(function() {
                        process($table);
                    });

                } else if ($(this).data('filter') && $(this).data('filter') == 'date') {

                    filter.append("<input type='text' data-field='"+ index +"' class='filter datepicker' />");
                    filter.change(function() {
                        process($table);
                    });

                } else if (!$(this).data('filter')) {

                    filter.append("<input type='text' data-field='"+ index +"' class='filter' />");
                    filter.keyup(function() {
                        process($table);
                    });

                }
            }

            filters.append(filter);
        });

        $('thead', $table).append(filters);
    }

    function reset($table) {
        $("tbody.raw-body tr", $table).removeClass('hide');
        $("tbody:not(.raw-body, .empty)", $table).remove();
        $("div.pagination ul").empty();
        $("tbody.empty", $table).hide();
    }

    function apply($table) {
        $(".filter", $table).each(function() {
            var select = $(this).hasClass('filter-select');
            var value = $(this).val().trim();
            var field = $(this).data('field');

            $("tbody.raw-body tr td:nth-child("+ (field+1) +")").each(function() {
                if (value != '') {
                    if (select) {
                        if ($(this).text() != value) {
                            $(this).parent().addClass('hide');
                        }
                    } else {
                        var pattern = '.*';
                        var words = value.split(' ');
                        words.forEach(function(e) {
                            pattern += '(' + e.trim() + ').*';
                        });

                        var regex = new RegExp(pattern, 'i');

                        var title = '';
                        var tooltip = '';

                        if ($(this).children('abbr').prop('title'))
                            tooltip = $(this).children().prop('title');

                        if ($(this).children('abbr').data('original-title'))
                            tooltip = $(this).children().data('original-title');

                        var matchText = $(this).text().search(regex);
                        var matchTitle = title.search(regex);
                        var matchTooltip = tooltip.search(regex);

                        if (matchText == -1 && matchTitle == -1 && matchTooltip == -1) {
                            $(this).parent().addClass('hide');
                        }
                    }
                }
            });
        });
    }

    function render($table) {
        var nb = $("tbody.raw-body tr:not(.hide)", $table).length;
        var perPage = $table.data('perpage');
        var nbPages = Math.ceil(nb / perPage);

        if (nb == 0) {
            $("tbody.empty", $table).show();
        }

        for (var i = 1; i <= nbPages; i++) {
            var tbody = $('<tbody>');
            tbody.attr('id', 'page-'+i);
            tbody.addClass('tab-pane');
            var page = $("<li><a data-toggle='tab'></a></li>");

            if (i == 1) {
                tbody.addClass('active');
                page.addClass('active');
            }

            tbody.append(
                $("tbody.raw-body tr:not(.hide)", $table).slice(perPage*(i-1), perPage*i).clone()
            );

            $table.append(tbody);

            page.children('a').attr('href', '#page-'+i);
            page.children('a').text(i);
            $("div.pagination ul").append(page);
        }
    }

    function process($table) {
        reset($table);
        apply($table);
        render($table);
        $table.trigger("filterUpdate");
    }

    $.fn.tablefilter = function() {

        var $table = $(this);
        init($table);
        process($table);

        if ($table.hasClass('sortable')) {
            $table.bind("sortEnd", function() {
                process($table);
            });
        }
    }

}(jQuery));
