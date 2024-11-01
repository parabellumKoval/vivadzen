/*
*
* Backpack Crud / List
*
*/

jQuery(function($){

    'use strict';

    var table = new DataTable('#crudTable');
 
    $('#datatable-go-to-page-btn').on('click', function () {
        var to_page = +$('#datatable-page-input').val()
        var to_page_num = to_page > 0? to_page - 1: 0;

        if(to_page_num !== 'undefined' && to_page_num !== null) {
          table.page(to_page_num).draw('page');
          $('#datatable-page-input').val(null)
        }
    });
});
