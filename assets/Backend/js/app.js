function initSlimscroll() {
  $('.slimscroll').slimscroll({
    height: 'auto',
    position: 'right',
    size: '7px',
    color: '#e0e5f1',
    opacity: 1,
    wheelStep: 5,
    touchScrollStep: 50
  });
}

function initMetisMenu() {
  $('.metismenu').metisMenu();
}

function initLeftMenuCollapse() {
  $('.button-menu-mobile').on('click', function (event) {
    event.preventDefault();
    $('body').toggleClass('enlarge-menu');
    initSlimscroll();
  });
}

function initEnlarge() {
  if ($(window).width() < 1025) {
    $('body').addClass('enlarge-menu');
  }
}

function initTooltipPlugin(){
  $.fn.tooltip && $('[data-toggle="tooltip"]').tooltip();
}

function initActiveMenu() {
  $('.left-sidenav a').each(function () {
    var pageUrl = window.location.href.split(/[?#]/)[0];
    if (this.href == pageUrl) {
      $(this).addClass('active');
      $(this).parent().addClass('active');
      $(this).parent().parent().addClass('in');
      $(this).parent().parent().addClass('mm-show');
      $(this).parent().parent().parent().addClass('mm-active');
      $(this).parent().parent().prev().addClass('active');
      $(this).parent().parent().parent().addClass('active');
      $(this).parent().parent().parent().parent().addClass('mm-show');
      $(this).parent().parent().parent().parent().parent().addClass('mm-active');
    }
  });
}

/****************************************************************/

window.theme = window.theme || {}
let datatable = '#datatable';
let datatable2 = '#datatable2';
let datatable3 = '#datatable3';
let datatable4 = '#datatable4';
let loadingId = '#global-loader';
let loadingText = '<div class="loader-text"><div class="spinner-border text-primary" role="status"><span class="sr-only"></span></div><br />' + theme.strings.loading + '</div>';

// Language Dropdown
function initLanguage() {
  $('body').on('click', '.language-dropdown .dropdown-item', function() {
    let $lang = $(this);
    $lang.closest('.input-group').find('input').addClass('d-none');
    $lang.closest('.input-group').find('input[data-lang=' + $lang.attr('data-lang') + ']').removeClass('d-none');
    $lang.closest('.input-group').find('.dropdown-toggle span').html($lang.text());
  });

  // Language Text Editor
  $('.language-editor-dropdown .dropdown-item').click(function() {
    let $lang = $(this);
    $lang.closest('.input-group').find('.lang-editor').addClass('d-none');
    $lang.closest('.input-group').find('.lang-editor[data-lang=' + $lang.attr('data-lang') + ']').removeClass('d-none');
    $lang.closest('.input-group').find('.dropdown-toggle span').html($lang.text());
  });
}

// Default Form
function initCustomForm() {
  if ($('.custom-form').length > 0) {

    let $custom_form;
    let $custom_button_click = $('.custom-form button');

    if ($('.custom-form').length > 1) {
      $custom_form = $(this);
    } else {
      $custom_form = $('.custom-form');
    }

    $custom_button_click.click(function() {
      let $custom_form_id = this.id;

      $custom_form.ajaxForm({
        type: $custom_form.attr('method'),
        url: $custom_form.attr('action'),
        data: $custom_form.serialize(),
        dataType: 'JSON',
        beforeSend: function() {
          $custom_button_click.prop('disabled', true);
          $('#' + $custom_form_id + ' .loading-spinner').removeClass('d-none');
          $('#' + $custom_form_id + ' .button-text').addClass('d-none');
        },
        success: function(result) {
          if (result.success) {
            window.location.href = result.url;
          } else {
            swal({
              title: theme.strings.error.title,
              text: result.error,
              type: 'error',
              html: true,
              dangerMode: false,
              closeOnClickOutside: false,
              confirmButtonText: theme.strings.close
            });
          }
        },
        complete: function() {
          $custom_button_click.prop('disabled', false);
          $('#' + $custom_form_id + ' .loading-spinner').addClass('d-none');
          $('#' + $custom_form_id + ' .button-text').removeClass('d-none');
        }
      });
    });
  }
}

// Datatable
function initDatatable() {
  let table_ajax;
  let $table = $(datatable);
  if ($table.length > 0) {
    table_ajax = $($table).DataTable({
      lengthChange: true,
      displayLength: 25,
      pageLength: 25,
      searching: false,
      info: true,
      dom: 'Blfrtip',
      buttons: [],
      responsive: false,
      processing: true,
      order: [],
      serverSide: true,
      destroy: true,
      ordering: true,
      language: {
        sDecimal: theme.datatable.decimal,
        sEmptyTable: theme.datatable.noRecord,
        sInfo: theme.datatable.info,
        sInfoEmpty: theme.datatable.infoEmpty,
        sInfoFiltered: theme.datatable.infoFiltered,
        sInfoPostFix: theme.datatable.infoPostFix,
        sInfoThousands: theme.datatable.infoThousands,
        sLengthMenu: theme.datatable.lengthMenu,
        sLoadingRecords: theme.datatable.loadingRecords,
        sProcessing: theme.datatable.processing,
        sSearch: theme.datatable.search,
        sZeroRecords: theme.datatable.zeroRecords,
        oPaginate: {
          sFirst: theme.datatable.pagination.first,
          sLast: theme.datatable.pagination.last,
          sNext: theme.datatable.pagination.next,
          sPrevious: theme.datatable.pagination.previous
        },
        buttons: {
          copyTitle: theme.datatable.copy,
          copySuccess: theme.datatable.copyTotal
        },
        paginate: {
          previous: '<i class="mdi mdi-chevron-left">',
          next: '<i class="mdi mdi-chevron-right">'
        }
      },
      drawCallback: function() {
        $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        $('[data-toggle="tooltip"]').tooltip();
      },
      ajax:{
        url: $table.data('table-url'),
        type: 'GET',
        dataType: 'JSON',
        data: function(d) {
          let form_data = $('form#search-form').serializeArray();
          $.each(form_data, function(key, val){
            d[val.name] = val.value;
          });
        }
      },
      columnDefs: [{
        targets: ['no-sort'],
        orderable: false
      }]
		});

    $('#filterSearch').click(function() {
      table_ajax.ajax.reload();
      $('html, body').animate({
        scrollTop: $('#datatable_wrapper').offset().top - 80
      }, 'slow');
    });

    $('#clearFilter').click(function() {
      $('#search-form').get(0).reset();
      $('#search-form select').val('').trigger('change');
      table_ajax.ajax.reload();
    });
  }
}

function initDatatable2() {
  let table_ajax;
  let $table = $(datatable2);
  if ($table.length > 0) {
    table_ajax = $($table).DataTable({
      lengthChange: true,
      displayLength: 25,
      pageLength: 25,
      searching: false,
      info: true,
      dom: 'Blfrtip',
      buttons: [],
      responsive: false,
      processing: true,
      order: [],
      serverSide: true,
      destroy: true,
      ordering: true,
      language: {
        sDecimal: theme.datatable.decimal,
        sEmptyTable: theme.datatable.noRecord,
        sInfo: theme.datatable.info,
        sInfoEmpty: theme.datatable.infoEmpty,
        sInfoFiltered: theme.datatable.infoFiltered,
        sInfoPostFix: theme.datatable.infoPostFix,
        sInfoThousands: theme.datatable.infoThousands,
        sLengthMenu: theme.datatable.lengthMenu,
        sLoadingRecords: theme.datatable.loadingRecords,
        sProcessing: theme.datatable.processing,
        sSearch: theme.datatable.search,
        sZeroRecords: theme.datatable.zeroRecords,
        oPaginate: {
          sFirst: theme.datatable.pagination.first,
          sLast: theme.datatable.pagination.last,
          sNext: theme.datatable.pagination.next,
          sPrevious: theme.datatable.pagination.previous
        },
        buttons: {
          copyTitle: theme.datatable.copy,
          copySuccess: theme.datatable.copyTotal
        },
        paginate: {
          previous: '<i class="mdi mdi-chevron-left">',
          next: '<i class="mdi mdi-chevron-right">'
        }
			},
      drawCallback: function(){
        $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        $('[data-toggle="tooltip"]').tooltip();
      },
      ajax:{
        url: $table.data('table-url'),
        type: 'GET',
        dataType: 'JSON'
      },
      columnDefs: [{
        targets: ['no-sort'],
        orderable: false
      }]
    });
  }
}

function initDatatable3() {
  let table_ajax;
  let $table = $(datatable3);
  if ($table.length > 0) {
    table_ajax = $($table).DataTable({
      lengthChange: true,
      displayLength: 25,
      pageLength: 25,
      searching: false,
      info: true,
      dom: 'Blfrtip',
      buttons: [],
      responsive: false,
      processing: true,
      order: [],
      serverSide: true,
      destroy: true,
      ordering: true,
      language: {
        sDecimal: theme.datatable.decimal,
        sEmptyTable: theme.datatable.noRecord,
        sInfo: theme.datatable.info,
        sInfoEmpty: theme.datatable.infoEmpty,
        sInfoFiltered: theme.datatable.infoFiltered,
        sInfoPostFix: theme.datatable.infoPostFix,
        sInfoThousands: theme.datatable.infoThousands,
        sLengthMenu: theme.datatable.lengthMenu,
        sLoadingRecords: theme.datatable.loadingRecords,
        sProcessing: theme.datatable.processing,
        sSearch: theme.datatable.search,
        sZeroRecords: theme.datatable.zeroRecords,
        oPaginate: {
          sFirst: theme.datatable.pagination.first,
          sLast: theme.datatable.pagination.last,
          sNext: theme.datatable.pagination.next,
          sPrevious: theme.datatable.pagination.previous
        },
        buttons: {
          copyTitle: theme.datatable.copy,
          copySuccess: theme.datatable.copyTotal
        },
        paginate: {
          previous: '<i class="mdi mdi-chevron-left">',
          next: '<i class="mdi mdi-chevron-right">'
        }
      },
      drawCallback: function(){
        $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        $('[data-toggle="tooltip"]').tooltip();
      },
      ajax:{
        url: $table.data('table-url'),
        type: 'GET',
        dataType: 'JSON'
      },
      columnDefs: [{
        targets: ['no-sort'],
        orderable: false
      }]
    });
  }
}

function initDatatable4() {
  let table_ajax;
  let $table = $(datatable4);
  if ($table.length > 0) {
    table_ajax = $($table).DataTable({
      lengthChange: true,
      displayLength: 25,
      pageLength: 25,
      searching: false,
      info: true,
      dom: 'Blfrtip',
      buttons: [],
      responsive: false,
      processing: true,
      order: [],
      serverSide: true,
      destroy: true,
      ordering: true,
      language: {
        sDecimal: theme.datatable.decimal,
        sEmptyTable: theme.datatable.noRecord,
        sInfo: theme.datatable.info,
        sInfoEmpty: theme.datatable.infoEmpty,
        sInfoFiltered: theme.datatable.infoFiltered,
        sInfoPostFix: theme.datatable.infoPostFix,
        sInfoThousands: theme.datatable.infoThousands,
        sLengthMenu: theme.datatable.lengthMenu,
        sLoadingRecords: theme.datatable.loadingRecords,
        sProcessing: theme.datatable.processing,
        sSearch: theme.datatable.search,
        sZeroRecords: theme.datatable.zeroRecords,
        oPaginate: {
          sFirst: theme.datatable.pagination.first,
          sLast: theme.datatable.pagination.last,
          sNext: theme.datatable.pagination.next,
          sPrevious: theme.datatable.pagination.previous
        },
        buttons: {
          copyTitle: theme.datatable.copy,
          copySuccess: theme.datatable.copyTotal
        },
        paginate: {
          previous: '<i class="mdi mdi-chevron-left">',
          next: '<i class="mdi mdi-chevron-right">'
        }
      },
      drawCallback: function(){
        $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        $('[data-toggle="tooltip"]').tooltip();
      },
      ajax:{
        url: $table.data('table-url'),
        type: 'GET',
        dataType: 'JSON'
      },
      columnDefs: [{
        targets: ['no-sort'],
        orderable: false
      }]
    });
  }
}

function initDatatableClearFilter() {
  if ($('#search-form').length > 0) {
    $('#search-form').get(0).reset();
    $('#search-form select').val('').trigger('change');
    $(datatable).DataTable().ajax.reload();
  }
}

// Datatable Delete
function initDatatableDelete() {
  $(document).on('click', '.datatableDelete', function() {
    let data_url = $(this).attr('data-url');

    swal({
      title: theme.strings.warning.title,
      text: theme.strings.warning.description,
      type: 'info',
      html: true,
      confirmButtonText: theme.strings.button.accept,
      cancelButtonText: theme.strings.button.cancel,
      showCancelButton: true,
      closeOnConfirm: false,
      showLoaderOnConfirm: true
    }, function() {
      setTimeout(function() {

        $.ajax({
          type: 'POST',
          url: data_url,
          success: function(result) {
            if (result.success) {
              initDatatableClearFilter();

              if ($(datatable).length > 0) {
                $(datatable).DataTable().ajax.reload();
              }

              if ($(datatable2).length > 0) {
                $(datatable2).DataTable().ajax.reload();
              }

              if ($(datatable3).length > 0) {
                $(datatable3).DataTable().ajax.reload();
              }

              swal(theme.strings.success.title, theme.strings.success.description, 'success');
            } else {
              swal({
                title: theme.strings.error.title,
                text: result.error,
                type: 'error',
                html: true,
                dangerMode: false,
                closeOnClickOutside: false,
                confirmButtonText: theme.strings.close
              });
            }
          }
        });

      }, 2000);
    });
  });
}

// Custom Delete
function initCustomDelete() {
  $(document).on('click', '.customDelete', function() {
    let url = $(this).attr('data-url');

    swal({
      title: theme.strings.warning.title,
      text: theme.strings.warning.description,
      type: 'warning',
      html: true,
      confirmButtonText: theme.strings.button.accept,
      cancelButtonText: theme.strings.button.cancel,
      showCancelButton: true,
      closeOnConfirm: false,
      showLoaderOnConfirm: true
    },
    function() {
      setTimeout(function() {

        if (url != undefined) {
          $.ajax({
            type: 'POST',
            url: url,
            success: function(result) {
              if (result.success) {
                window.location.href = result.url;
              } else {
                swal({
                  title: theme.strings.error.title,
                  text: result.error,
                  type: 'error',
                  html: true,
                  dangerMode: false,
                  closeOnClickOutside: false,
                  confirmButtonText: theme.strings.close
                });
              }
            }
          });
        }

      }, 2000);
    });
  });
}

// Confirmation
function initConfirmation() {
	$(document).on('click', '.confirmation', function() {
		let url = $(this).attr('data-url');

		swal({
			title: theme.strings.warning.title,
			text: theme.strings.warning.description,
			type: 'warning',
			html: true,
			confirmButtonText: theme.strings.button.accept,
			cancelButtonText: theme.strings.button.cancel,
			showCancelButton: true,
			closeOnConfirm: false,
			showLoaderOnConfirm: true
		},
		function() {
			setTimeout(function() {

				if (url != undefined) {
					$.ajax({
						type: 'POST',
						url: url,
						success: function(result) {
							if (result.success) {
								window.location.href = result.url;
							} else {
								swal({
									title: theme.strings.error.title,
									text: result.error,
									type: 'error',
									html: true,
									dangerMode: false,
									closeOnClickOutside: false,
									confirmButtonText: theme.strings.close
								});
							}
						}
					});
				}

			}, 2000);
		});
	});
}

// Select 2
function initSelect2() {
  if ($('.select2').length > 0) {
    $('.select2').select2({
      language: {
        noResults: function(params) {
          return theme.select2.noRecord;
        }
      }
    });
  }
}

// Date Picker
function initDatePicker() {
  if ($('.datepicker').length > 0) {
    $('.datepicker').daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      minYear: 1940,
      maxYear: moment(),
      autoUpdateInput: false
    }, function(chosen_date) {
      $(this.element[0]).val(chosen_date.format('DD/MM/YYYY'));
    });
  }
}

// Maxlength
function initMaxlength() {
  if ($('.maxlength').length > 0) {
    $('.maxlength').maxlength({
      alwaysShow: true,
      warningClass: 'badge badge-info',
      limitReachedClass: 'badge badge-warning'
    });
  }
}

// Auto Change
function initAutoChange() {
  $('body').on('change', '[data-change]', function() {
    let $this = $(this);
    let $grid = $($this.attr('data-change'));
    $grid.prop('disabled', true);

    if ($this.val() != '') {
      $.ajax({
        url: $('base').attr('href') + '/callback',
        type: 'POST',
        data: {
          action: 'select-auto-change',
          get: $this.attr('data-get'),
          parent: $this.val()
        },
        success: function(result) {
          $grid.append('<option value="">' + theme.strings.choose + '</option>');

          $grid.empty();
          $('#city_id').append('<option value="">' + theme.strings.choose + '</option>');
          $.each(result.list, function(key, val) {
            $grid.append(
            '<option value="' + val.id + '">' + val.name + '</option>'
            );
          });

          $grid.removeAttr('disabled');

          if ($this.attr('data-get') == 'city_id') {
            $('#district_id option').remove();
            $('#district_id').append('<option value="">' + theme.strings.choose + '</option>');
          }
        }
      });
    } else {
      $grid.html('<option value="">' + theme.strings.choose + '</option>').prop('disabled', false);

      if ($this.attr('data-get') == 'city_id') {
        $('#district_id option').remove();
        $('#district_id').append('<option value="">' + theme.strings.choose + '</option>');
      }
    }
  });
}

// Dropify
function initDropify() {
  $('.dropify').dropify({
    messages: {
      'default': theme.dropify.messages.default,
      'replace': theme.dropify.messages.replace,
      'remove': theme.dropify.messages.remove,
      'error': theme.dropify.messages.error
    }
  });

  // File Deleted
  let drEvent = $('.dropify').dropify();

  drEvent.on('dropify.afterClear', function(event, element) {
    let url = $(this).attr('data-url');
    if (url != undefined) {
      $.ajax({
        type: 'POST',
        url: url,
        success: function(result) {
          if (result.success) {
            swal(theme.strings.success.title, theme.strings.success.description, 'success');
          } else {
            swal({
              title: theme.strings.error.title,
              text: result.error,
              type: 'error',
              html: true,
              dangerMode: false,
              closeOnClickOutside: false,
              confirmButtonText: theme.strings.close
            });
          }
        }
      });
    }
  });
}

// Date Range Picker
function initDateRangePicker() {
  if ($('.daterange').length > 0) {
    $('.daterange').daterangepicker({
      opens: 'left',
      autoUpdateInput: false,
      locale: {
        applyLabel: theme.strings.apply,
        cancelLabel: theme.strings.clear
      }
    });

    $('.daterange').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });

    $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
    });
  }
}

// Date Time Picker
function initDateTimePicker() {
  if ($('.datetime-picker').length > 0) {
    $('.datetime-picker').bootstrapMaterialDatePicker({
      format: 'DD/MM/YYYY HH:mm'
    });
  }
}

// Multi Select
function initMultiSelectFunction() {
	if ($('.multi-select').length > 0) {
		$('.multi-select').lwMultiSelect();
	}
}

// Color Picker
function initColorPicker() {
  if ($('.color-picker').length > 0) {
    $('.color-picker').colorpicker();
  }
}

// Nestable
function initNestable() {
  if ($('#nestable').length > 0) {
    $(document).ready(function() {
      let updateOutput = function(e) {
        let list = e.length ? e : $(e.target),
            output = list.data('output');
        if (output) {
          if (window.JSON) {
            output.val(window.JSON.stringify(list.nestable('serialize')));
            updateSort(window.JSON.stringify(list.nestable('serialize')));
          } else {
            output.val('JSON browser support required for this demo.');
          }
        }
      };

      $('#nestable').nestable().on('change', updateOutput);
      updateOutput($('#nestable').data('output', $('#nestable-output')));
    });

    function lagXHRobjekt() {
      let XHRobjekt = null;
      let ajaxRequest;
      try {
        ajaxRequest = new XMLHttpRequest();
      } catch(err1) {
        try {
          ajaxRequest = new ActiveXObject('Microsoft.XMLHTTP');
        } catch(err2) {
          try {
            ajaxRequest = new ActiveXObject('Msxml2.XMLHTTP');
          } catch(err3) {
            ajaxRequest = false;
          }
        }
      }
      return ajaxRequest;
    }

    function updateSort(jsonstring) {
      let mittXHRobjekt = lagXHRobjekt();
      if (mittXHRobjekt) {
        $.ajax({
          url: $('#nestable').attr('data-update-url') + '?jsonstring=' + jsonstring + '&rand=' + Math.random()*9999,
          type: 'GET'
        });
      }
    }

    $.ajax({
      type: 'GET',
      url: $('#nestable').attr('data-url'),
      beforeSend: function(){
        $('#nestable').html('<div class="text-center"><span class="spinner-border text-primary"></span><br />' + theme.strings.loading + '</div>');
      },
      success: function(result) {
        if (result.success) {
          $('#nestable').html(result.success);
        } else {
          $('#nestable').html(result.error);
        }
      }
    });
  }
}

// Sortable
function initSortable() {
  if ($('#sortable').length > 0) {
    let data_url = $('#sortable').attr('data-url');

    $('#sortable').sortable({
      handle: '.sort-by-box',
      update: function(event, ui) {
        let sorted = $('#sortable').sortable('serialize');

        $.ajax({
          type: 'POST',
          url: data_url,
          data: sorted
        });
      }
    });
  }
};

// Ajax Modal
function modalPage(modalType, data) {
  $(loadingId).show();

  $.ajax({
    type: 'POST',
    url: $('base').attr('href') + '/callback',
    data: {
      action: 'modal',
      modalType: modalType,
      data: data
    },
    beforeSend: function() {
      $(loadingId).html(loadingText);
    },
    success: function(result) {
      if (result.success) {
        $(loadingId).hide();
        $('.tooltip').remove();

        if (result.modal_size == 'xl') {
          $('.ajax-modal-content .modal-dialog').removeClass('modal-lg');
          $('.ajax-modal-content .modal-dialog').addClass('modal-xl');
        }

        $('.ajax-modal-content .modal-header h6').html(result.title);

        $('.ajax-modal-content').modal({
          backdrop: 'static',
          keyboard: false
        });

        $('.ajax-modal-content').modal('show');
        $('.ajax-modal-content .modal-body').html(result.content);

        // Fuctions
        initLanguage();
        initCustomForm();
        initSelect2();
        initDatePicker();
        initAutoChange();
        initMultiSelect();
        initDropify();
        directoratesFileForm();
        newsParagraphsForm();
        eventParagraphsForm();
        showcaseModuleProductForm();

        // CK Editor
        if ($('#news_paragraph_description').length > 0) {
          CKEDITOR.replace('news_paragraph_description');
        }

        if ($('#event_paragraph_description').length > 0) {
          CKEDITOR.replace('event_paragraph_description');
        }

      } else {
        swal({
          title: theme.strings.error.title,
          text: result.error,
          type: 'error',
          html: true,
          dangerMode: false,
          closeOnClickOutside: false,
          confirmButtonText: theme.strings.close
        });
      }
    },
    complete: function() {
      $(loadingId).hide();
    }
  });
};

// Random Code
function initRandomCode(type) {
  let length = 10;
  let result = '';
  let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let charactersLength = characters.length;
  for (let i = 0; i < length; i++ ) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  $('#' + type).val(result);
}

// Convert to Slug
function convertToSlug(str) {
	str = str.replace(/^\s+|\s+$/g, '');
  str = str.toLowerCase();

  // Remove
  var from = "ãàáäâçẽèéëêğıìíïîoõòóöôşuùúüûñç·/_,:;";
  var to   = "aaaaaceeeeegiiiiioooooosuuuuunc______";
  for (var i = 0, l = from.length; i < l; i++) {
    str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
  }

  str = str.replace(/[^a-z0-9 _]/g, '').replace(/\s+/g, '_').replace(/-+/g, '_');
  return str;
}

// Fine Uploader
function initFineUploader() {
  if ($('#showcase-picture-grid').length > 0) {
    let page_url = $('#tab-pictures').attr('data-page-url');
    LoadList();

    // Subject Control
    $(document).on('click', '.qq-upload-button-selector input', function(event) {
      if ($('.fine-uploader-name .lang-editor[data-lang="' + theme.parameter.lang + '"] input').val() == '') {
        swal({
          title: theme.strings.error.title,
          text: theme.fineUploader.messages.uploadError,
          type: 'error',
          html: true,
          dangerMode: false,
          closeOnClickOutside: false,
          confirmButtonText: theme.strings.close
        });
        return false;
      }
    });

    function LoadList() {
      let $grid = $('#showcase-picture-grid');

      $.ajax({
        cache: false,
        type: 'POST',
        url: $('base').attr('href') + '/' + page_url + '/images/list/' + $('#tab-pictures').attr('data-id'),
        success: function(result) {

          $grid.empty();
          $.each(result.image_list, function(key, val) {

            let html = '';
            if (val.type == 'projects') {
              let checked1 = val.default == theme.parameter.formCheckboxValueNumber ? 'checked' : '';
              html += '<div class="mt-2"><label for="project_image_default_' + val.id + '" class="form-label mb-lg-0 mb-10">' + val.defaultLang + '</label><div class="custom-switch switch-primary mb-4"><input type="checkbox" name="project_image_default" id="project_image_default_' + val.id + '" class="custom-control-input" value="' + theme.parameter.formCheckboxValueNumber + '" ' + checked1 + ' /><label for="project_image_default_' + val.id + '" class="custom-control-label"></label></div></div>';
            }

            let saveButton = '';
            if (val.type == 'projects') {
              if (val.update == true) {
                saveButton = '<button type="button" class="btn btn-purple btn-sm save-showcase-picture mt-4">' +
                '<i class="far fa-save"></i> ' + theme.strings.button.save +
                '</button>';
              }
            }

            $grid.append(
            '<div class="col-sm-12 col-md-3 showcase-picture-item gallery-item" data-id="' + val.id + '">' +
            '	<div class="thumbnail">' +
            '		<span class="badge badge-danger delete-showcase-picture"><i class="fa fa-times"></i></span>' +
            '		<div class="caption">' +
            '			<form action="' + $('base').attr('href') + '/' + page_url + '/images/update/' + val.id + '" method="POST">' +
            '				<img src="' + val.image + '" title="" alt="" />' +
            html +
            saveButton +
            '			</form>' +
            '		</div>' +
            '	</div>' +
            '</div>'
            );

            $('[data-toggle="tooltip"]').tooltip();
          });

          /* Update */
          $('.save-showcase-picture').click(function() {
            let $form = $(this).closest('form');

            $.ajax({
              cache: false,
              type: $form.prop('method'),
              url: $form.prop('action'),
              data: $form.serializeArray(),
              success: function(result) {
                if (result.success) {
                  swal(theme.strings.success.title, theme.strings.success.description, 'success');
                } else {
                  swal({
                    title: theme.strings.error.title,
                    text: result.error,
                    type: 'error',
                    html: true,
                    dangerMode: false,
                    closeOnClickOutside: false,
                    confirmButtonText: theme.strings.close
                  });
                }
              }
            });
          });

          /* Delete */
          $('.delete-showcase-picture').click(function() {
            let $el = $(this);
            let $div = $el.closest('.showcase-picture-item');
            let postData = {
              id: $div.data('id')
            };

            swal({
              title: theme.strings.warning.title,
              text: theme.strings.warning.description,
              type: 'warning',
              html: true,
              confirmButtonText: theme.strings.button.accept,
              cancelButtonText: theme.strings.button.cancel,
              showCancelButton: true,
              closeOnConfirm: false,
              showLoaderOnConfirm: true
            },
            function() {
              setTimeout(function() {
                $.ajax({
                  cache: false,
                  type: 'GET',
                  url: $('base').attr('href') + '/' + page_url + '/images/delete/',
                  data: postData,
                  success: function(result) {
                    if (result.success) {
                      swal(theme.strings.success.title, result.success, 'success');

                      $div.hide(250, function() {
                        $div.remove();
                      });
                    } else {
                      swal({
                        title: theme.strings.error.title,
                        text: result.error,
                        type: 'error',
                        html: true,
                        dangerMode: false,
                        closeOnClickOutside: false,
                        confirmButtonText: theme.strings.close
                      });
                    }
                  }
                });
              }, 2000);
            });
          });

          /* Sort */
          $grid.sortable({
            handle: 'img',
            update: function(event, ui) {
              $('.showcase-picture-item').each(function(index) {
                let sortData = {
                  id: $(this).data('id'),
                  index: index
                };

                $.ajax({
                  cache: false,
                  type: 'GET',
                  url: $('base').attr('href') + '/' + page_url + '/images/sort',
                  data: sortData,
                  success: function() {}
                });
              });
            }
          }).disableSelection();
        }
      });
    }

    $('#file-upload-area').fineUploader({
      request: {
        endpoint: $('base').attr('href') + '/' + page_url + '/images/upload/' + $('#tab-pictures').attr('data-id')
      },
      template: 'qq-template',
      multiple: true
    }).on('complete', function(event, id, name, result) {

      if (result.success) {
        LoadList();
      } else {
        swal({
          title: theme.strings.error.title,
          text: result.error,
          type: 'error',
          html: true,
          dangerMode: false,
          closeOnClickOutside: false,
          confirmButtonText: theme.strings.close
        });
      }

    }).on('submit', function(event, id, filename) {
      $(this).fineUploader('setParams', {
        'slug': convertToSlug($('.fine-uploader-name .lang-editor[data-lang="' + theme.parameter.lang + '"] input').val())
      });
    });
  }
}

// International Telephone
function initInternationalTelephone() {
	if ($('.phone').length > 0) {
		$('.phone').intlTelInput({
			nationalMode: false,
			preferredCountries: [theme.parameter.lang],
			preventInvalidDialCodes: true
		});
	}
};

// Multi Select
function initMultiSelect() {
	if ($('.searchable').length > 0) {
		$('.searchable').multiSelect({
			selectableHeader: "<input type='text' class='form-control search-input mb-2' autocomplete='off' placeholder='" + theme.strings.search + "' />",
			selectionHeader: "<input type='text' class='form-control search-input mb-2' autocomplete='off' placeholder='" + theme.strings.search + "' />",
			afterInit: function(ms) {
				let that = this,
					$selectableSearch = that.$selectableUl.prev(),
					$selectionSearch = that.$selectionUl.prev(),
					selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
					selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

				that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
				.on('keydown', function(e) {
					if (e.which === 40) {
						that.$selectableUl.focus();
						return false;
					}
				});

				that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
				.on('keydown', function(e) {
					if (e.which == 40) {
						that.$selectionUl.focus();
						return false;
					}
				});
			},
			afterSelect: function() {
				this.qs1.cache();
				this.qs2.cache();
			},
			afterDeselect: function() {
				this.qs1.cache();
				this.qs2.cache();
			}
		});
	}
}

// Password Show
if ($('.password-show').length > 0) {
  $('.password-show').find('.form-control').each(function(index, input) {
    let $input = $(input);
    $input.parent().find('.input-group-prepend').click(function() {
      let change = '';
      if ($(this).find('i').hasClass('fa-eye')) {
        $(this).find('i').removeClass('fa-eye');
        $(this).find('i').addClass('fa-eye-slash');
        change = 'text';
      } else {
        $(this).find('i').removeClass('fa-eye-slash');
        $(this).find('i').addClass('fa-eye');
        change = 'password';
      }

      let rep = $("<input type='" + change + "' />")
                .attr('id', $input.attr('id'))
                .attr('name', $input.attr('name'))
                .attr('class', $input.attr('class'))
                .val($input.val())
                .insertBefore($input);
      $input.remove();
      $input = rep;
    }).insertAfter($input);
  });
};

// Design Settings (Menu Type)
$('#menu_type').on('change', function() {
  let type = $(this).select2().find(':selected').data('type');

  console.log(type);

  if (type == 'page') { // Pages
    $('.menu-pages').removeClass('d-none');
    $('.menu-sultangazi-contents').addClass('d-none');
    $('.menu-contracts').addClass('d-none');
    $('.menu-services').addClass('d-none');
    $('.menu-projects').addClass('d-none');
    $('.menu-president-contents').addClass('d-none');
    $('.menu-link').addClass('d-none');

    $('.menu-sultangazi-contents select').val('');
    $('.menu-contracts select').val('');
    $('.menu-services select').val('');
    $('.menu-projects select').val('');
    $('.menu-president-contents select').val('');
    $('.menu-link input').val('');
  } else if (type == 'sultangazi_content') { // Sultangazi Contents
    $('.menu-pages').addClass('d-none');
    $('.menu-sultangazi-contents').removeClass('d-none');
    $('.menu-contracts').addClass('d-none');
    $('.menu-services').addClass('d-none');
    $('.menu-projects').addClass('d-none');
    $('.menu-president-contents').addClass('d-none');
    $('.menu-link').addClass('d-none');

    $('.menu-pages select').val('');
    $('.menu-contracts select').val('');
    $('.menu-services select').val('');
    $('.menu-projects select').val('');
    $('.menu-president-contents select').val('');
    $('.menu-link input').val('');
  } else if (type == 'contract') { // Contracts
    $('.menu-pages').addClass('d-none');
    $('.menu-sultangazi-contents').addClass('d-none');
    $('.menu-contracts').removeClass('d-none');
    $('.menu-services').addClass('d-none');
    $('.menu-president-contents').addClass('d-none');
    $('.menu-projects').addClass('d-none');
    $('.menu-link').addClass('d-none');

    $('.menu-pages select').val('');
    $('.menu-sultangazi-contents select').val('');
    $('.menu-services select').val('');
    $('.menu-projects select').val('');
    $('.menu-president-contents select').val('');
    $('.menu-link input').val('');
  } else if (type == 'projects') { // Projects
    $('.menu-pages').addClass('d-none');
    $('.menu-sultangazi-contents').addClass('d-none');
    $('.menu-contracts').addClass('d-none');
    $('.menu-projects').removeClass('d-none');
    $('.menu-services').addClass('d-none');
    $('.menu-president-contents').addClass('d-none');
    $('.menu-link').addClass('d-none');

    $('.menu-pages select').val('');
    $('.menu-sultangazi-contents select').val('');
    $('.menu-contracts select').val('');
    $('.menu-services select').val('');
    $('.menu-president-contents select').val('');
    $('.menu-link input').val('');
  } else if (type == 'service') { // Services
    $('.menu-pages').addClass('d-none');
    $('.menu-sultangazi-contents').addClass('d-none');
    $('.menu-contracts').addClass('d-none');
    $('.menu-services').removeClass('d-none');
    $('.menu-projects').addClass('d-none');
    $('.menu-president-contents').addClass('d-none');
    $('.menu-link').addClass('d-none');

    $('.menu-pages select').val('');
    $('.menu-sultangazi-contents select').val('');
    $('.menu-contracts select').val('');
    $('.menu-projects select').val('');
    $('.menu-president-contents select').val('');
    $('.menu-link input').val('');
  } else if (type == 'president_content') { // President Contents
    $('.menu-pages').addClass('d-none');
    $('.menu-sultangazi-contents').addClass('d-none');
    $('.menu-contracts').addClass('d-none');
    $('.menu-projects').addClass('d-none');
    $('.menu-services').addClass('d-none');
    $('.menu-president-contents').removeClass('d-none');
    $('.menu-link').addClass('d-none');

    $('.menu-pages select').val('');
    $('.menu-sultangazi-contents select').val('');
    $('.menu-contracts select').val('');
    $('.menu-projects select').val('');
    $('.menu-link input').val('');
  } else if (type == 'link') { // Link
    $('.menu-pages').addClass('d-none');
    $('.menu-sultangazi-contents').addClass('d-none');
    $('.menu-contracts').addClass('d-none');
    $('.menu-projects').addClass('d-none');
    $('.menu-services').addClass('d-none');
    $('.menu-president-contents').addClass('d-none');
    $('.menu-link').removeClass('d-none');

    $('.menu-pages select').val('');
    $('.menu-sultangazi-contents select').val('');
    $('.menu-contracts select').val('');
    $('.menu-projects select').val('');
    $('.menu-services select').val('');
    $('.menu-president-contents select').val('');
  }
});

// Organization Chart
$('#organization_chart_type').on('change', function() {
  let type = $(this).select2().find(':selected').data('type');

  if (type == 'president') { // President
    $('.menu-president').removeClass('d-none');
    $('.menu-vice-presidents').addClass('d-none');
    $('.menu-directorates').addClass('d-none');
    $('.menu-link').addClass('d-none');

    $('.menu-vice-presidents select').val('');
    $('.menu-directorates select').val('');
    $('.menu-link input').val('');
  } else if (type == 'vice_presidents') { // Vice Presidents
    $('.menu-president').addClass('d-none');
    $('.menu-vice-presidents').removeClass('d-none');
    $('.menu-directorates').addClass('d-none');
    $('.menu-link').addClass('d-none');

    $('.menu-president select').val('');
    $('.menu-directorates select').val('');
    $('.menu-link input').val('');
  } else if (type == 'directorates') { // Directorates
    $('.menu-president').addClass('d-none');
    $('.menu-vice-presidents').addClass('d-none');
    $('.menu-directorates').removeClass('d-none');
    $('.menu-link').addClass('d-none');

    $('.menu-president select').val('');
    $('.menu-vice-presidents select').val('');
    $('.menu-link input').val('');
  } else if (type == 'link') { // Link
    $('.menu-president').addClass('d-none');
    $('.menu-vice-presidents').addClass('d-none');
    $('.menu-directorates').addClass('d-none');
    $('.menu-link').removeClass('d-none');

    $('.menu-president select').val('');
    $('.menu-vice-presidents select').val('');
    $('.menu-directorates select').val('');
  }
});

// Directorates File Form
function directoratesFileForm() {
	if ($('.directorates-file-form').length > 0) {
		let $custom_form = $('.directorates-file-form');
		let $custom_button_click = $('.directorates-file-form button[type="submit"]');

		$custom_button_click.click(function(e) {
			e.preventDefault();

      let $custom_form_id = this.id;
      let form = $custom_form[0];
      let formData = new FormData(form);

			$.ajax({
				type: $custom_form.attr('method'),
				url: $custom_form.attr('action'),
        data: formData,
				dataType: 'JSON',
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() {
          $custom_button_click.prop('disabled', true);
          $('#' + $custom_form_id + ' .loading-spinner').removeClass('d-none');
          $('#' + $custom_form_id + ' .button-text').addClass('d-none');
        },
				success: function(result) {
					if (result.success) {
						$(datatable2).DataTable().ajax.reload();
						$('.ajax-modal-content').modal('hide');
					} else {
						swal({
							title: theme.strings.error.title,
							text: result.error,
							type: 'error',
							html: true,
							dangerMode: false,
							closeOnClickOutside: false,
							confirmButtonText: theme.strings.close
						});
					}
				},
        complete: function() {
          $custom_button_click.prop('disabled', false);
          $('#' + $custom_form_id + ' .loading-spinner').addClass('d-none');
          $('#' + $custom_form_id + ' .button-text').removeClass('d-none');
        }
			});
		});
	}
}

// News Paragraphs Form
function newsParagraphsForm() {
	if ($('.news-paragraphs-form').length > 0) {
		let $custom_form = $('.news-paragraphs-form');
		let $custom_button_click = $('.news-paragraphs-form button[type="submit"]');

		$custom_button_click.click(function(e) {
			e.preventDefault();

      // CK Editor
      for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
      }

      let $custom_form_id = this.id;
      let form = $custom_form[0];
      let formData = new FormData(form);

			$.ajax({
				type: $custom_form.attr('method'),
				url: $custom_form.attr('action'),
        data: formData,
				dataType: 'JSON',
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() {
          $custom_button_click.prop('disabled', true);
          $('#' + $custom_form_id + ' .loading-spinner').removeClass('d-none');
          $('#' + $custom_form_id + ' .button-text').addClass('d-none');
        },
				success: function(result) {
					if (result.success) {
						$(datatable2).DataTable().ajax.reload();
						$('.ajax-modal-content').modal('hide');
					} else {
						swal({
							title: theme.strings.error.title,
							text: result.error,
							type: 'error',
							html: true,
							dangerMode: false,
							closeOnClickOutside: false,
							confirmButtonText: theme.strings.close
						});
					}
				},
        complete: function() {
          $custom_button_click.prop('disabled', false);
          $('#' + $custom_form_id + ' .loading-spinner').addClass('d-none');
          $('#' + $custom_form_id + ' .button-text').removeClass('d-none');
        }
			});
		});
	}
}

// Event Paragraphs Form
function eventParagraphsForm() {
	if ($('.event-paragraphs-form').length > 0) {
		let $custom_form = $('.event-paragraphs-form');
		let $custom_button_click = $('.event-paragraphs-form button[type="submit"]');

		$custom_button_click.click(function(e) {
			e.preventDefault();

      // CK Editor
      for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
      }

      let $custom_form_id = this.id;
      let form = $custom_form[0];
      let formData = new FormData(form);

			$.ajax({
				type: $custom_form.attr('method'),
				url: $custom_form.attr('action'),
        data: formData,
				dataType: 'JSON',
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() {
          $custom_button_click.prop('disabled', true);
          $('#' + $custom_form_id + ' .loading-spinner').removeClass('d-none');
          $('#' + $custom_form_id + ' .button-text').addClass('d-none');
        },
				success: function(result) {
					if (result.success) {
						$(datatable2).DataTable().ajax.reload();
						$('.ajax-modal-content').modal('hide');
					} else {
						swal({
							title: theme.strings.error.title,
							text: result.error,
							type: 'error',
							html: true,
							dangerMode: false,
							closeOnClickOutside: false,
							confirmButtonText: theme.strings.close
						});
					}
				},
        complete: function() {
          $custom_button_click.prop('disabled', false);
          $('#' + $custom_form_id + ' .loading-spinner').addClass('d-none');
          $('#' + $custom_form_id + ' .button-text').removeClass('d-none');
        }
			});
		});
	}
}

// Showcase Module Product Form
function showcaseModuleProductForm() {
	if ($('.showcase-module-product-form').length > 0) {
		let $custom_form = $('.showcase-module-product-form');
		let $custom_button_click = $('.showcase-module-product-form button[type="submit"]');

		$custom_button_click.click(function(e) {
			e.preventDefault();

			$.ajax({
				type: $custom_form.attr('method'),
				url: $custom_form.attr('action'),
				data: $custom_form.serialize(),
				dataType: 'JSON',
				success: function(result) {
					if (result.success) {
						$(datatable).DataTable().ajax.reload();
						$('.ajax-modal-content').modal('hide');
					} else {
						swal({
							title: theme.strings.error.title,
							text: result.error,
							type: 'error',
							html: true,
							dangerMode: false,
							closeOnClickOutside: false,
							confirmButtonText: theme.strings.close
						});
					}
				}
			});
		});
	}
}

// Showcase Module Product Form Edit
if ($('.showcaseModuleProductFormEdit').length > 0) {
	$(datatable).on('draw.dt', function() {

		if (!$(datatable + ' tbody tr td').hasClass('dataTables_empty')) {
			$(datatable).Tabledit({
				url: $('.showcaseModuleProductFormEdit').attr('data-table-action-url'),
				dataType: 'JSON',
				columns: {
					identifier : [0, 'id'],
					editable:[[3, 'order']]
				},
				onSuccess: function(result, textStatus, jqXHR) {
					if (result.success) {
						if (result.action == 'remove') {
							$(datatable).DataTable().ajax.reload();

							if (result.total == 0) {
								datatable.column(4).visible(false);
							}
						}
					} else {
						swal({
							title: theme.strings.error.title,
							text: result.error,
							type: 'error',
							html: true,
							dangerMode: false,
							closeOnClickOutside: false,
							confirmButtonText: theme.strings.close
						});
					}
				}
			});
		}

	});
}

/****************************************************************/

// Checkbox Select All
function checkboxSelectAll() {
	if ($('#selectall').length > 0) {
		$('#selectall').click(function(event) {
			if (this.checked) {
				$('.checkbox-control:checkbox').attr('checked', true);
			} else {
				$('.checkbox-control:checkbox').attr('checked', false);
			}
		});
	}
}

// Selected Export
$('.selected-export input[name="pdf_export"]').on('click', function() {

  let selected = new Array();
  $('input:checkbox[name*="choose"]:checked').each(function() {
    selected.push($(this).val());
  });

  $(loadingId).show();
  $export_form = $('.export-form');

  $.ajax({
    type: $export_form.attr('method'),
    url: $export_form.attr('action'),
    data: { selected: selected, type: $(this).attr('name') },
    beforeSend: function() {
      $(loadingId).html(loadingText);
    },
    success: function(result) {
      if (result.success) {
        $(loadingId).hide();

        $('.ajax-modal-content .modal-header h6').html(result.title);

        $('.ajax-modal-content').modal({
          backdrop: 'static',
          keyboard: false
        });

        $('.ajax-modal-content').modal('show');

        let link = document.createElement('a');
				document.body.appendChild(link);
				link.setAttribute('type', 'hidden');
				link.href = result.base_64;
				link.download = result.file_name;
				link.click();
				document.body.removeChild(link);

				$('.modal-body').text(result.success);
      } else {
        swal({
          title: theme.strings.error.title,
          text: result.error,
          type: 'error',
          html: true,
          dangerMode: false,
          closeOnClickOutside: false,
          confirmButtonText: theme.strings.close
        });
      }
    },
    complete: function() {
      $(loadingId).hide();
    }
  });
});

// Product Search (Price)
$('#product_price_type').on('change', function() {
  let type = $('#product_price_type option:selected').val();

  if (type == 'purchase' || type == 'sale') {
    $('#price_min').prop('disabled', false);
    $('#price_max').prop('disabled', false);
  } else {
    $('#price_min').val('');
    $('#price_max').val('');
    $('#price_min').prop('disabled', true);
    $('#price_max').prop('disabled', true);
  }
});

// Popup Module
$('#popup_module_type').on('change', function() {
  let type = $(this).select2().find(':selected').data('type');

  if (type == 'html') {
    $('.popup_type_html').show();
    $('.popup_type_image').hide();
  } else {
    $('.popup_type_html').hide();
    $('.popup_type_image').show();
  }
});

// Design Settings (Menu Parent)
$('#menu_parent_id').on('change', function() {
	let value = $('#menu_parent_id option:selected').val();

	if (value == 0) { // Parent Menu
		$('.menu-template').show();
	} else {
		$('.menu-template').hide();
	}
});

// Map Module Locations
$('#map_type_id').on('change', function() {
	let type = $('#map_type_id option:selected').val();

	if (type == theme.parameter.mapModule.types.type1) {
		$('.map-module-type .tx-danger').show();
    $('.map-projects').addClass('d-none');

    $('.map-module-type .tx-danger').removeClass('d-none');
    $('.map-module-type .tx-danger').addClass('d-inline');
	} else {
		$('.map-module-type .tx-danger').hide();
    $('.map-projects').removeClass('d-none');

    $('.map-module-type .tx-danger').addClass('d-none');
    $('.map-module-type .tx-danger').removeClass('d-inline');
	}
});

// Google Map Locations
function googleMapLocations() {
  if ($('#googleMap').length > 0) {
  	let mapProp = {
      center: new google.maps.LatLng(41.10447876417359, 28.873059312031398),
      zoom: 14,
  	};

  	let marker = new google.maps.Marker({
  		draggable: false
  	});

  	let map = new google.maps.Map(document.getElementById('googleMap'), mapProp);
  	google.maps.event.addListener(map, 'click', function(event) {
  		// Marker
  		marker.setPosition(event.latLng);
      marker.setMap(map);

  		document.getElementById('latitude').value = event.latLng.lat();
  		document.getElementById('longitude').value = event.latLng.lng();
  	});
  }
}

function init() {
  initSlimscroll();
  initMetisMenu();
  initLeftMenuCollapse();
  initEnlarge();
  initTooltipPlugin();
  initActiveMenu();
  Waves.init();
  initLanguage();
  initCustomForm();
  initDatatable();
  initDatatable2();
  initDatatable3();
  initDatatable4();
  initDatatableClearFilter();
  initDatatableDelete();
  initCustomDelete();
  initConfirmation();
  initSelect2();
  initDatePicker();
  initMaxlength();
  initAutoChange();
  initDropify();
  initDateRangePicker();
  initDateTimePicker();
  initMultiSelectFunction();
  initColorPicker();
  initNestable();
  initSortable();
  initRandomCode();
  initFineUploader();
  initInternationalTelephone();
  initMultiSelect();
  checkboxSelectAll();
}

init();
