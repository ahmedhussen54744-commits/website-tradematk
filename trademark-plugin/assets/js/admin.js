/**
 * Trademark Certificate Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // ===== MEDIA UPLOADER =====
        $(document).on('click', '.tm-upload-btn', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var target = button.data('target');
            var showPreview = button.data('preview') === 1;
            
            var frame = wp.media({
                title: 'Select File',
                button: { text: 'Use This File' },
                multiple: false
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#' + target).val(attachment.url);
                
                if (showPreview) {
                    var preview = button.closest('.tm-file-field').find('.tm-file-preview');
                    preview.html('<img src="' + attachment.url + '" style="max-width:300px;border:1px solid #ddd;padding:5px;margin-bottom:10px;display:block;">');
                }
                
                // Add remove button if not exists
                if (button.siblings('.tm-remove-btn').length === 0) {
                    button.after('<button type="button" class="button tm-remove-btn" data-target="' + target + '">Remove</button>');
                }
            });
            
            frame.open();
        });
        
        // Remove file
        $(document).on('click', '.tm-remove-btn', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            $('#' + target).val('');
            $(this).closest('.tm-file-field').find('.tm-file-preview').html('');
            $(this).remove();
        });
        
        // ===== QUICK APPROVE =====
        $(document).on('click', '.tm-quick-approve', function(e) {
            e.preventDefault();
            var btn = $(this);
            var postId = btn.data('id');
            
            if (!confirm('Approve this application?')) return;
            
            btn.prop('disabled', true).text('...');
            
            $.post(tmCertAdmin.ajaxUrl, {
                action: 'tm_cert_quick_action',
                nonce: tmCertAdmin.nonce,
                post_id: postId,
                tm_action: 'approve'
            }, function(response) {
                if (response.success) {
                    btn.closest('tr').fadeOut(400, function() { $(this).remove(); });
                } else {
                    alert('Error: ' + response.data);
                    btn.prop('disabled', false).text('Approve');
                }
            });
        });
        
        // ===== QUICK REJECT =====
        $(document).on('click', '.tm-quick-reject', function(e) {
            e.preventDefault();
            var btn = $(this);
            var postId = btn.data('id');
            
            if (!confirm('Reject this application?')) return;
            
            btn.prop('disabled', true).text('...');
            
            $.post(tmCertAdmin.ajaxUrl, {
                action: 'tm_cert_quick_action',
                nonce: tmCertAdmin.nonce,
                post_id: postId,
                tm_action: 'reject'
            }, function(response) {
                if (response.success) {
                    btn.closest('tr').fadeOut(400, function() { $(this).remove(); });
                } else {
                    alert('Error: ' + response.data);
                    btn.prop('disabled', false).text('Reject');
                }
            });
        });
        
        // ===== SELECT ALL =====
        $('#tmSelectAll').on('change', function() {
            $('.tm-bulk-check').prop('checked', $(this).prop('checked'));
        });
        
        // ===== BULK ACTION =====
        $('#tmBulkApply').on('click', function() {
            var action = $('#tmBulkSelect').val();
            if (!action) {
                alert('Please select an action.');
                return;
            }
            
            var ids = [];
            $('.tm-bulk-check:checked').each(function() {
                ids.push($(this).val());
            });
            
            if (ids.length === 0) {
                alert('Please select at least one item.');
                return;
            }
            
            if (!confirm('Apply "' + action + '" to ' + ids.length + ' item(s)?')) return;
            
            $.post(tmCertAdmin.ajaxUrl, {
                action: 'tm_cert_bulk_action',
                nonce: tmCertAdmin.nonce,
                ids: ids,
                tm_action: action
            }, function(response) {
                if (response.success) {
                    alert(response.data.count + ' items updated.');
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        });
        
        // ===== EXPORT CSV =====
        $('#tmExportBtn').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).text('Exporting...');
            
            $.post(tmCertAdmin.ajaxUrl, {
                action: 'tm_cert_export',
                nonce: tmCertAdmin.nonce
            }, function(response) {
                if (response.success) {
                    var csv = '';
                    response.data.data.forEach(function(row) {
                        csv += row.map(function(cell) {
                            return '"' + (cell || '').replace(/"/g, '""') + '"';
                        }).join(',') + '\n';
                    });
                    
                    var blob = new Blob([csv], { type: 'text/csv' });
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'trademark-certificates-' + new Date().toISOString().slice(0,10) + '.csv';
                    a.click();
                    window.URL.revokeObjectURL(url);
                }
                btn.prop('disabled', false).text('Export CSV');
            });
        });
        
    });

})(jQuery);
