<?php
/* GLOBAL VARIABLES */
$prevText = '';
$prevInput = null;
$nextText = '';
$editTable = '';
$builtElements = 0;
$stripLabels = false;

/* RECURSIVE FUNCTION TO FIND ALL FORM ELEMENTS */
function showDOMNode( DOMNode $domNode ) {

    global $prevText;
    global $prevInput;
    global $nextText;
    global $editTable;
    global $builtElements;
    global $stripLabels;

    foreach( $domNode->childNodes as $node )
    {
        $nName = $node->nodeName;
        $nValue = trim( str_replace( ':', '', $node->nodeValue ) );
        $nType = $node->nodeType;

        /* SOME FORMS WHEN COPY/PASTED INTO THE SYSTEM HAVE UNRECOGNIZABLE LINE
           BREAKS AND WHITE SPACE THAT CAN'T BE REMOVED WITH REGEX \s
           SO HERE WE CHECK EACH INDIVIDUALS CHARACTER OF EACH PIECE OF THE NODE VALUE
           THIS WAY WE CAN INSURE WHEN NORMAL CHARACTERS ARE DETECTED WE HAVE AN ACTUAL
           STRING OF CHARACTERS */
        if ( 3 == $nType ) {
            $allchars = str_split( $nValue );
            foreach( $allchars as $achar ) {
                if( ctype_alnum ( $achar ) ) {
                    $prevText = $nValue;
                    if( $prevInput ) {
                        $nextText = $nValue;
                    }
                    break;
                }
            }
        } elseif ( XML_ELEMENT_NODE == $nType ) {
            if( $stripLabels ) {
                $prevText = '';
                $nextText = '';
            }
            if ( $nName == 'input' && ( $node->getAttribute('type') == 'text' || $node->getAttribute('type') == 'tel' || $node->getAttribute('type') == 'email' || $node->getAttribute('type') == 'password' )) {
                $editTable .= '<li class="ui-state-default"><div><input type="checkbox" name="' . 'fb_check_' . $builtElements . '" checked="checked" /></div>';
                $editTable .= '<div>' . buildInput( 'fb_label_' . $builtElements, $prevText ) . '</div>';
                $editTable .= '<div>' . buildInput( 'fb_element_' . $builtElements, $node->getAttribute( 'name' ), true );
                $editTable .= '<br /><input type="button" value="<-- Replace with ^^" onclick="valReplace(' . $builtElements . ')" />';
                $editTable .= '</div>';
                $editTable .= '<div>' . buildInput( 'fb_value_' . $builtElements, $nValue ) . '</div><input type="hidden" class="wpfi_field_type" value="text" /><br /></li>';
                $builtElements++;
            }
            if ( $nName == 'select' ) {
                $editTable .= '<li class="ui-state-default"><div><input type="checkbox" name="' . 'fb_check_' . $builtElements . '" checked="checked" /></div>';
                $editTable .= '<div>' . buildInput( 'fb_label_' . $builtElements, $prevText ) . '</div>';
                $editTable .= '<div>' . buildInput( 'fb_element_' . $builtElements, $node->getAttribute( 'name' ), true ) . '</div>';
                $editTable .= '<div>' . buildSelect( 'fb_value_' . $builtElements, $node ) . '</div><input type="hidden" class="wpfi_field_type" value="select" /><br /></li>';
                $builtElements++;
            }
            if ( $nName == 'textarea' ) {
                $editTable .= '<li class="ui-state-default"><div><input type="checkbox" name="' . 'fb_check_' . $builtElements . '" checked="checked" /></div>';
                $editTable .= '<div>' . buildInput( 'fb_label_' . $builtElements, $prevText ) . '</div>';
                $editTable .= '<div>' . buildInput( 'fb_element_' . $builtElements, $node->getAttribute( 'name' ), true ) . '</div>';
                $editTable .= '<div>' . buildTextarea( 'fb_value_' . $builtElements, $nValue ) . '</div><input type="hidden" class="wpfi_field_type" value="textarea" /><br /></li>';
                $builtElements++;
            }

            if ( $prevInput && '' != $nextText && $prevInput->getAttribute('type') == 'checkbox' ) {
                $dumpVar = $prevInput->ownerDocument->saveXML($prevInput);
                $xmlVersion = simplexml_load_string($dumpVar);
                $editTable .= '<li class="ui-state-default"><div><input type="checkbox" name="' . 'fb_check_' . $builtElements . '" checked="checked" /></div>';
                $editTable .= '<div>' . buildInput( 'fb_label_' . $builtElements, $nextText ) . '</div>';
                $editTable .= '<div>' . buildInput( 'fb_element_' . $builtElements, $prevInput->getAttribute( 'name' ), true ) . '</div>';
                $editTable .= '<div>' . buildCheckbox( 'fb_value_' . $builtElements, xml_attribute( $xmlVersion, 'value' ) ) . '</div><input type="hidden" class="wpfi_field_type" value="checkbox" /><br /></li>';

                /* clear variables so we are prepared for next input that requires label on right */
                $nextText = '';
                $prevInput = null;
                $builtElements++;
            }
            if ( $nName == 'input' && $node->getAttribute( 'type' ) == 'checkbox' ) {
                /* Typically labels for checkboxes follow the input, here we save our input for use after. */
                $prevInput = $node;
            }

            if ( $prevInput && '' != $nextText && $prevInput->getAttribute('type') == 'radio' ) {
                /*
                 * all DOM objects are invisible to var_dump() and print_r() supposedly because they are C objects
                 * and not PHP objects. saveXML() works fine on DOMDocument, but is not implemented on DOMElement.
                 * Below gives an xml representation of DOMElement
                 */
                $dumpVar = $prevInput->ownerDocument->saveXML($prevInput);
                $xmlVersion = simplexml_load_string($dumpVar);
                $editTable .= '<li class="ui-state-default"><div><input type="checkbox" name="' . 'fb_check_' . $builtElements . '" checked="checked" /></div>';
                $editTable .= '<div>' . buildInput( 'fb_label_' . $builtElements, $nextText ) . '</div>';
                $editTable .= '<div>' . buildInput( 'fb_element_' . $builtElements, $prevInput->getAttribute( 'name' ), true ) . '</div>';
                $editTable .= '<div>' . buildRadio( 'fb_value_' . $builtElements, xml_attribute( $xmlVersion, 'value' ), $prevInput->getAttribute( 'name' ) ) . '</div><input type="hidden" class="wpfi_field_type" value="radio" /><br /></li>';

                /* clear variables so we are prepared for next input that requires label on right */
                $nextText = '';
                $prevInput = null;
                $builtElements++;
            }
            if ( $nName == 'input' && $node->getAttribute( 'type' ) == 'radio' ) {
                //var_dump($node);
                /* Typically labels for checkboxes follow the input, here we save our input for use after. */
                $prevInput = $node;
            }
        }

        if( $node->hasChildNodes() ) {
            showDOMNode( $node );
        }
    }
}

//Process our dom and return it
function parseTable( $dom, $strip ) {
    global $editTable;
    global $stripLabels;
    $stripLabels = $strip;
    showDOMNode( $dom );
    return $editTable;
}

/* INPUT ELEMENT BUILDING FUNCTIONS */
function buildInput( $inputName, $inputValue = '' , $disabled = false ) {
    $inputOff = '';
    if( $disabled ) {
        $inputOff = ' disabled="disabled"';
    }
    $inputBuilt = '<input type="text" id="' . $inputName . '" name="' . $inputName . '" value="' . $inputValue . '" ' . $inputOff . ' />';
    return $inputBuilt;
}

function buildTextarea( $inputName, $inputValue = '' ) {
    $inputBuilt = '<textarea name="' . $inputName . '" value="' . $inputValue . '"></textarea>';
    return $inputBuilt;
}

function buildSelect( $selectName, DOMNode $domNode ) {
    $selectBuilt = '<select name="' . $selectName . '">';
    foreach( $domNode->childNodes as $node ) {
        $selectBuilt .= '<option value="' . $node->nodeValue . '"">' . $node->nodeValue . '</option>';
    }
    $selectBuilt .= '</select>';
    return $selectBuilt;
}

function buildCheckbox( $checkboxName, $checkboxValue = '' ) {
    $checkboxBuilt = '<input type="checkbox" value="' . $checkboxValue . '" name="' . $checkboxName . '" />';
    return $checkboxBuilt;
}

function buildRadio( $radioTrueValue, $radioValue = '', $radioName ) {
    $radioBuilt = '<input type="radio" value="' . $radioValue . '" name="' . $radioName . '" />';
    return $radioBuilt;
}

//pull an attribute from an xml element
function xml_attribute($object, $attribute)
{
    if( isset( $object[$attribute] ) )
        return (string) $object[$attribute];
}