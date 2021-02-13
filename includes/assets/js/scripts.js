jQuery(document).ready(
    function () {
        jQuery('.dt2_update_fields').on(
            'click',
            function (e) {
                e.preventDefault();

                // Get section type.
                let $section_type = '.' + jQuery(this).attr('id');
                let $action = jQuery(this).attr('data-action');

                // Reset.
                dt2_reset_notices($section_type);

                // Action variables.
                let $this = jQuery(this);
                let $nonce = jQuery($section_type + ' .dt2_page .dt2_add_update_nonce').val();
                let $ajax_url = jQuery($section_type + ' .dt2_page .dt2_add_update_nonce_ajax_url').val();
                let $dt2_options = [];
                let $dt2_items = jQuery($section_type + ' .dt2_page .item-content');

                // Show spinner.
                dt2_spinner($section_type);
                $this.addClass('dt2_disabled');

                // Verify nonce and ajax URL.
                if (!$nonce || !$ajax_url) {
                    // Return notice.
                    dt2_return_notice($section_type, 'An error occurred, please refresh to try again or contact 2414.', 'notice-error');
                    return;
                }

                // Validate selects.
                if (!dt2_validate_select_lists($section_type)) {
                    // Return notice.
                    dt2_return_notice($section_type, 'All created lists must have a DT and MC list', 'notice-error');

                    // Enable btn.
                    $this.removeClass('dt2_disabled');
                    return;
                }

                // Organize options.
                $dt2_items.each(function () {
                    let dt2_item = {};
                    let dt2_item_dt_key = jQuery(this).find('.dt2_dt_field').val();
                    let dt2_item_dt_value = jQuery(this).find('.dt2_dt_field option:selected').text();
                    let dt2_item_mc_key = jQuery(this).find('.dt2_mc_field').val();
                    let dt2_item_mc_value = jQuery(this).find('.dt2_mc_field option:selected').text();

                    dt2_item.dt_key = dt2_item_dt_key;
                    dt2_item.dt_value = dt2_item_dt_value;
                    dt2_item.mc_key = dt2_item_mc_key;
                    dt2_item.mc_value = dt2_item_mc_value;

                    $dt2_options.push(dt2_item);
                });

                // Validate list duplicates.
                if (dt2_validate_duplicate_select_lists($dt2_options)) {
                    // Return notice.
                    dt2_return_notice($section_type, 'A specific list can only be selected once.', 'notice-error');

                    // Enable btn.
                    $this.removeClass('dt2_disabled');
                    return;
                }

                // Fetch posts through AJAX.
                jQuery.ajax(
                    {
                        url: $ajax_url,
                        type: "POST",
                        dataType: "json",
                        data: {
                            nonce: $nonce,
                            action: $action,
                            dt2_options: $dt2_options,
                        },
                        success: function (response) {
                            if (response.success) {
                                // Return notice.
                                dt2_return_notice($section_type, 'DT - MC Options were updated successfully.', 'notice-success');

                                // Remove spinner.
                                dt2_spinner($section_type, false);

                                // Enable btn.
                                $this.removeClass('dt2_disabled');
                            } else {
                                dt2_return_notice($section_type, 'An error occurred, please refresh to try again or contact 2414.', 'notice-error');
                            }
                        },
                        error: function () {
                            dt2_return_notice($section_type, 'An error occurred, please refresh to try again or contact 2414.', 'notice-error');
                        }
                    }
                );
            }
        );
    }
);
