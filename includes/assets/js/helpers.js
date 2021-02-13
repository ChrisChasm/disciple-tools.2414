function dt2_reset_notices($section_type)
{
    // General Variables.
    var $notice      = jQuery( $section_type+' .dt2_notice' );
    var $spinner     = jQuery( $section_type+' .dt2_spinner' );

    // Reset Notices.
    $notice.find( 'p' ).html( '' );
    $notice.removeClass( 'notice-error' );
    $notice.removeClass( 'notice-warning' );
    $notice.removeClass( 'notice-success' );

    $notice.slideUp( 'fast' );
    $spinner.slideUp( 'fast' );
}

function dt2_return_notice($section_type, $msg, $notice_type)
{
    // General Variables.
    var $spinner = jQuery( $section_type+' .dt2_spinner' );
    var $notice  = jQuery( $section_type+' .dt2_notice' );

    if ($msg && $notice_type) {
        $notice.find( 'p' ).html( $msg );
        $notice.addClass( $notice_type );
        $notice.slideDown( 'fast' );
        $spinner.slideUp( 'fast' );
    }
}

function dt2_spinner($section_type, show = true)
{
    var $spinner = jQuery( $section_type+' .dt2_spinner' );
    console.log($spinner);
    if(show){
        $spinner.slideDown( 'fast' );
    }else{
        $spinner.slideUp( 'fast' );
    }
}

function dt2_validate_select_lists($section_type) {
    let dt2Valid = true;
    jQuery($section_type+" .dt2_the_field").each(function () {
        if (!jQuery(this).val()) {
            dt2Valid = false;
        }
    });
    return dt2Valid;
}

function dt2_validate_duplicate_select_lists($dt2_options){
    var mcVals = $dt2_options.map(function(item){ return item.mc_key });
    var isDuplicateMc = mcVals.some(function(item, idx){
        return mcVals.indexOf(item) !== idx
    });

    var dtVals = $dt2_options.map(function(item){ return item.dt_key });
    var isDuplicateDt = dtVals.some(function(item, idx){
        return dtVals.indexOf(item) !== idx
    });

    return !!(isDuplicateMc || isDuplicateDt);
}