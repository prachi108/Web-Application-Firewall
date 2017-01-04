JQuery(document).ready(function(){
    JQuery(".bfQuickMode legend").each(
        function(){
            JQuery(this).prepend("<i class='icon-edit bfLegendIcon'></i> ");
        }
    );
});