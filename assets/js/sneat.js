// POUR PERFECT SCROLLBAR SNEAT
let perfectScrollbarElements = document.querySelectorAll('.perfect-scrollbar');
perfectScrollbarElements.forEach(function (el) {
    new PerfectScrollbar(el, {
        wheelPropagation: false,
        wheelSpeed: .3
    });
});

let perfectScrollbarVerticalElements = document.querySelectorAll('.perfect-scrollbar-vertical');
perfectScrollbarVerticalElements.forEach(function (el) {
    new PerfectScrollbar(el, {
        suppressScrollX: true,
        wheelPropagation: false,
        wheelSpeed: .3
    });
});

let perfectScrollbarHorizontalElements = document.querySelectorAll('.perfect-scrollbar-horizontal');
perfectScrollbarHorizontalElements.forEach(function (el) {
    new PerfectScrollbar(el, {
        suppressScrollY: true,
        wheelPropagation: false,
        wheelSpeed: .3
    });
});

//    POPOVER SNEAT
var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
});


// TOOLTIP SNEAT
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new Tooltip(tooltipTriggerEl);
});
