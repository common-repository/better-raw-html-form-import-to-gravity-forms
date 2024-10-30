jQuery( function( $ ) {
    $("#wpfi_editForm ul").sortable();
    $("#import_form_btn").click(function() { buildJson() });

    /* This function is called when the import button is clicked after a user is satisfied with their
    fields.  It takes all the fields the user has edited, converts it into json and sends off to the
    form importer of their choice (at this time just gravity forms)
     */
    function buildJson() {
        //Create an array for the fields to built in the order as sorted by the user
        var sorted_fields = new Array();
        var cnt = 0;

        //We will be handling text input for choice inputs differently
        var input_fields = ["text", "textarea"];
        var choice_fields = ["select", "radio", "checkbox"];
        var radios = new Array();

        //loop through each field's options
        $( "#wpfi_editForm ul li").each( function() {
            if($(this).find("div:nth-child(1) input").is(":checked")) {
                var input_type = $(this).find(".wpfi_field_type").val();
                var input_label = $(this).find("div:nth-child(2) input").val();

                //handle our input fields depending on type
                if (input_fields.indexOf(input_type) > -1) {
                    sorted_fields[cnt] = buildField(input_label, input_type, cnt);
                    cnt++;
                }
                if (choice_fields.indexOf(input_type) > -1) {
                    var input_options = new Array();
                    if (input_type == 'select') {
                        $(this).find("div:nth-child(4) select option").each(function () {
                            input_options.push({"text": $(this).text(), "value": $(this).val(), "isSelected": false});
                        });

                        sorted_fields[cnt] = buildOptionField(input_label, input_type, input_options, cnt);
                        cnt++;
                    } else if (input_type == 'checkbox') {
                        input_options.push({"text": $(this).text(), "value": $(this).text(), "isSelected": false});

                        sorted_fields[cnt] = buildOptionField(input_label, input_type, input_options, cnt);
                        cnt++;
                    } else if (input_type == 'radio' ) {
                        if(radios[$(this).find("div:nth-child(4) input").attr("name")] === undefined) {
                            radios[$(this).find("div:nth-child(4) input").attr("name")] = new Array();
                        }
                        radios[$(this).find("div:nth-child(4) input").attr("name")].push({"text": $(this).find("div:nth-child(2) input").val(), "value": $(this).find("div:nth-child(4) input").val(), "isSelected": false});
                    }
                }
            }
        });
        for(var k in radios) {
            sorted_fields[cnt] = buildOptionField(k, 'radio', radios[k], cnt);
            cnt++;
        }

        //created our primary object to be converted to JSON
        var form_title = "Imported Form";
        var form_desc = "Imported by WP Raw HTML Form Import";

        if($("#wpfi_form_title").val() != "") {
            form_title = $("#wpfi_form_title").val();
        }
        if($("#wpfi_form_title").val() != "") {
            form_desc = $("#wpfi_form_title").val();
        }
        var submit_form = {

            'title' : form_title,
            'description' : form_desc,
            'labelPlacement' : 'top_label',
            'button' : {
                'type':'text'
            },
            'confirmations' : {
                'name' : 'Default Confirmation',
                'type' : 'message',
                'message' : 'Thank you',
                'isDefault' : true
            },
            'fields' : sorted_fields
        }

        //call our form creation functions
        createForm(submit_form);
    }

    //altered found on https://www.stevenhenty.com/gravity-forms-api/
    //creates the json string and submits to gravity forms api (api url set in wp-better-form-importer-tool.php)
    function createForm(submit_form){
        var final_form = JSON.stringify(submit_form);
        $.ajax({
                url: $("#gf_api_url").val(),
                type: 'POST',
                data: '[' + final_form + ']'
            })
            .always(function (data) {
                var response_id = '';
                if(JSON.stringify(data).indexOf("Warning") > -1) {
                    //The gravity forms api will return php warnings sometimes which disrupts the
                    //ability to parse correctly so we have to hack it a bit
                    var split_off_warnings = JSON.stringify(data).split('response\\":[');
                    var split_off_ending = split_off_warnings[1].split("]");
                    response_id = split_off_ending[0];
                } else {
                    response_id = data.response[0];
                }
                window.location.href='admin.php?page=gf_edit_forms&id=' + response_id;
            });
    }
});

function buildField(input_label, input_type, cnt) {
    return {
        "type": input_type,
        "id": String(cnt),
        "label": input_label
    }
}

function buildOptionField(input_label, input_type, input_options, cnt) {
    return {
        "type": input_type,
        "id": String(cnt),
        "label": input_label,
        "choices": input_options
    }
}

function valReplace(bNum) {
    document.getElementById("fb_label_" + bNum).value = document.getElementById("fb_element_" + bNum).value;
}
