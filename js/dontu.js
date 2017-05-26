jQuery(document).ready(function($){
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    //toastr["success"]("This is configured ok!", "Ok");

    $(function(){
        $('#edit-main-title').editable({
            url: '?post',
            escape: true,
            title: 'Titlu',
            name: 'edit-title',
            defaultValue: '',
            value: $('#main-title').html(),
            display: false,
            ajaxOptions: {
                type: 'post',
                dataType: 'json'
            },
            success: function(response, newValue) {
                toastr[response.type]( response.message, response.type.capitalize() );
                if( response.type == 'success' ) {
                    $('#main-title').html( response.new_value );
                }
            }
        });

        $('#edit-sub-title').editable({
            url: '?post',
            escape: true,
            title: 'Sub titlu',
            name: 'edit-sub-title',
            defaultValue: '',
            value: $('#sub-title').html(),
            display: false,
            ajaxOptions: {
                type: 'post',
                dataType: 'json'
            },
            success: function(response, newValue) {
                toastr[response.type]( response.message, response.type.capitalize() );
                if( response.type == 'success' ) {
                    $('#sub-title').html( response.new_value );
                }
            }
        });

        $('#new-section-name').editable({
            pk: 1,
            url: '?post',
            escape: true,
            title: 'Denumire Sectiune',
            name: 'new-section-name',
            defaultValue: '',
            value: '',
            display: false,
            ajaxOptions: {
                 type: 'post',
                 dataType: 'json'
            },
            success: function(response, newValue) {
                toastr[response.type]( response.message, response.type.capitalize() );
                location.reload();
            }
        });
    });
});

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
};

function getHistoryData() {
    $.post('?post', { name: 'mock-history' }, function ( result ) {
        toastr[result.type]( result.message, result.type.capitalize() );
    }, 'json');
    return true;
}

function newSubElement( elem, parent_id ) {
    $(elem).editable({
        pk: 0,
        url: '?post',
        type: 'subsection',
        escape: true,
        placement: 'bottom',
        showbuttons: 'bottom',
        title: 'Adauga Sub Sectiune Noua',
        name: 'new-sub-section',
        value: {
            parent_id: parent_id,
            title: 'Titlu Sub Sectiune',
            content: 'Text'
        },
        display: false,
        toggle: 'manual',
        ajaxOptions: {
            type: 'post',
            dataType: 'json'
        },
        success: function(response, newValue) {
            toastr[response.type]( response.message, response.type.capitalize() );
            location.reload();
        }
    });
    $(elem).editable('toggle');
    return true;
}

function editSection( elem, id ) {
    $(elem).editable({
        pk: id,
        url: '?post',
        escape: true,
        placement: 'right',
        title: 'Modifica Sectiune',
        name: 'section-update',
        value: $('#section_name_'+id).html(),
        display: false,
        toggle: 'manual',
        ajaxOptions: {
            type: 'post',
            dataType: 'json'
        },
        success: function(response, newValue) {
            toastr[response.type]( response.message, response.type.capitalize() );
            location.reload();
        }
    });
    $(elem).editable('toggle');
    return true;
}

function editSubSection( elem, id, parent_id ) {
    $(elem).editable({
        pk: id,
        url: '?post',
        type: 'subsection',
        escape: true,
        placement: 'bottom',
        showbuttons: 'bottom',
        title: 'Modifica Subsectiune',
        name: 'edit-sub-section',
        value: {
            parent_id: parent_id,
            title: $('#sub_section_title_'+id).html(),
            content: $('#sub_section_content_'+id).html()
        },
        display: false,
        toggle: 'manual',
        ajaxOptions: {
            type: 'post',
            dataType: 'json'
        },
        success: function(response, newValue) {
            toastr[response.type]( response.message, response.type.capitalize() );
            location.reload();
        }
    });
    $(elem).editable('toggle');
    return true;
}

function deleteSection( id ) {
    $.post('?post', {name:'delete-section', id:id}, function( response ) {
        toastr[response.type]( response.message, response.type.capitalize() );
        location.reload();
    }, 'json');
    return true;
}

function deleteSubSection( id ) {
    $.post('?post', {name:'delete-sub-section', id:id}, function( response ) {
        toastr[response.type]( response.message, response.type.capitalize() );
        location.reload();
    }, 'json');
    return true;
}

(function ($) {
    "use strict";

    var Subsection = function (options) {
        this.init('subsection', options, Subsection.defaults);
    };
    //inherit from Abstract input
    $.fn.editableutils.inherit(Subsection, $.fn.editabletypes.abstractinput);

    $.extend(Subsection.prototype, {
        /**
         Renders input from tpl
         @method render()
         **/
        render: function() {
            this.$input = this.$tpl.find('input');
            this.$textarea = this.$tpl.find('textarea');
        },

        /**
         Default method to show value in element. Can be overwritten by display option.

         @method value2html(value, element)
         **/
        value2html: function(value, element) {
            if(!value) {
                $(element).empty();
                return;
            }
            var html = $('<div>').text(value.title).html() + ', ' + $('<div>').text(value.content).html()+ ', ' + $('<div>').text(value.parent_id).html();
            $(element).html(html);
        },

        /**
         Gets value from element's html

         @method html2value(html)
         **/
        html2value: function(html) {
            /*
             you may write parsing method to get value by element's html
             e.g. "Moscow, st. Lenina, bld. 15" => {city: "Moscow", street: "Lenina", building: "15"}
             but for complex structures it's not recommended.
             Better set value directly via javascript, e.g.
             editable({
             value: {
             city: "Moscow",
             street: "Lenina",
             building: "15"
             }
             });
             */
            return null;
        },

        /**
         Converts value to string.
         It is used in internal comparing (not for sending to server).

         @method value2str(value)
         **/
        value2str: function(value) {
            var str = '';
            if(value) {
                for(var k in value) {
                    str = str + k + ':' + value[k] + ';';
                }
            }
            return str;
        },

        /*
         Converts string to value. Used for reading value from 'data-value' attribute.

         @method str2value(str)
         */
        str2value: function(str) {
            /*
             this is mainly for parsing value defined in data-value attribute.
             If you will always set value by javascript, no need to overwrite it
             */
            return str;
        },

        /**
         Sets value of input.

         @method value2input(value)
         @param {mixed} value
         **/
        value2input: function(value) {
            if(!value) {
                return;
            }
            this.$input.filter('[name="parent_id"]').val(value.parent_id);
            this.$input.filter('[name="title"]').val(value.title);
            this.$textarea.filter('[name="content"]').val(value.content);
        },

        /**
         Returns value of input.

         @method input2value()
         **/
        input2value: function() {
            return {
                parent_id: this.$input.filter('[name="parent_id"]').val(),
                title: this.$input.filter('[name="title"]').val(),
                content: this.$textarea.filter('[name="content"]').val()
            };
        },

        /**
         Activates input: sets focus on the first field.

         @method activate()
         **/
        activate: function() {
            this.$input.filter('[name="title"]').focus();
            this.$textarea.wysihtml5({"html":true});
        },

        /**
         Attaches handler to submit form in case of 'showbuttons=false' mode

         @method autosubmit()
         **/
        autosubmit: function() {
            this.$input.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
            this.$textarea.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
        }
    });

    Subsection.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-subsection"><input type="hidden" name="parent_id"></div>'+
             '<div class="editable-subsection"><label><span>Titlu: </span></label><br/><input type="text" name="title" class="input-lg" style="width: 100%"></div><br/>'+
             '<div class="editable-subsection"><label><span>Continut:</span></label><br/><textarea name="content" style="width: 100%; height: 300px; border: 2px solid #EEE; padding: 8px;"></textarea></div>',

        inputclass: ''
    });

    $.fn.editabletypes.subsection = Subsection;

}(window.jQuery));