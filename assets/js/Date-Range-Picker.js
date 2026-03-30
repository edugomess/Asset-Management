$(function(){
    try {
        const dp = document.getElementById('datePicker');
        if (dp && typeof Lightpick !== 'undefined') {
            new Lightpick({
                field: dp,
                onSelect: function(date){ dp.value = date ? date.format('Do MMMM YYYY') : ''; }
            });
        }
    } catch(e) { console.error("Error initializing datePicker:", e); }
    
    try {
        const drp = document.getElementById('dateRangePicker');
        if (drp && typeof Lightpick !== 'undefined') {
            new Lightpick({
                field: drp,
                singleDate: false,
                onSelect: function(start, end){
                    let str = '';
                    str += start ? start.format('Do MMMM YYYY') + ' to ' : '';
                    str += end ? end.format('Do MMMM YYYY') : '...';
                    drp.value = str;
                }
            });
        }
    } catch(e) { console.error("Error initializing dateRangePicker:", e); }
});
