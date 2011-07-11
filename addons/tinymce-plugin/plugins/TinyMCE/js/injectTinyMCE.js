/* These lines fix some issues that arise when minify is enabled.
   tinyMCE determines it's URL for plugins and themes from script element,
   but when minify is enabled, tinyMCE can't find proper script element.
   So, we help it it a bit.
   That's why this script has to be executed BEFORE loading tinymce, since
   it would too late then to fix stuff.
   Why not using gdn.definition or gdn.url? Because definitions are not
   available at the moment when these lines get executed. Defenitions get
   loaded AFTER TinyMCE. */
(function () {
    var scripts = document.getElementsByTagName('script');
    for (i in scripts) {
        if (!scripts[i].src)
            continue;
        u = scripts[i].src;
        origin = document.location.protocol + "//" + document.location.host;
        if (u && (u.indexOf(origin) === 0)) {
            uri = u.substring(origin, u.lastIndexOf('/'));
            if (uri.search('/plugins/') != -1) {
                u = uri.substring(0, uri.search('/plugins/') + 9) + 'TinyMCE/js';
                window.tinyMCEPreInit = {
                    base: u,
                    suffix: "",
                    query: ""
                }
                break;
            }
        }
    }
})();

function toogleEditorMode(editor) {
    if (!editor.isVisible) {
        editor.isVisible = true;
    }

    if(editor.isVisible) {
        tinyMCE.removeMCEControl(tinyMCE.getEditorId(editor));
        editor.isVisible = false;
    } else {
        tinyMCE.addMCEControl(document.getElementById('pagecontent'), editor);
        editor.isVisible = true;
    }
}

$().ready(function() {
    var config = {
        language:                           gdn.definition('tinymceLang'),
        plugins:                            gdn.definition('tinymcePlugins') + ',inlinepopups',
        theme:                              "advanced",
        width:                              "663",
        theme_advanced_buttons1:            "bold,italic,underline,strikethrough,|,link,unlink,image,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo",
        theme_advanced_buttons2:            false,
        theme_advanced_buttons3:            false,
        theme_advanced_toolbar_location:    "top",
        theme_advanced_toolbar_align:       "left",
        theme_advanced_statusbar_location:  "bottom",
        theme_advanced_resizing:            true,
        theme_advanced_resizing_max_width:  "663",
        theme_advanced_resizing_min_width:  "663",
        theme_advanced_styles : "Code=codeStyle;Quote=quoteStyle",
        inlinepopups_skin:                  "clearlooks2",

        /*  As Vanilla preferes newlines to format the output, let's do
            everything to make formatting consistent */
        convert_newlines_to_brs:            false,
        apply_source_formatting:            true,
        remove_linebreaks:                  true,
        remove_redundant_brs:               true,
        inline_styles:                      false, // Vanilla strips all styles
        formats:                            {
            bold:          { inline: 'b'                },
            italic:        { inline: 'i'                },
            underline:     { inline: 'u',   exact: true  },
            strikethrough: { inline: 'del', exact: true }
        },
        extended_valid_elements: "blockquote[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick"
                                +"|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
                                +"|onmouseover|onmouseup|style|title|rel],",
        content_css: gdn.definition('tinymceEditorCSS'),
    }

    $("#Form_Comment #Form_Body, #DiscussionForm #Form_Body").livequery(function() {

        // The textarea element
        var FormBody = $(this);

        // The parent div element for the editor area
        var CommentForm = FormBody.parents("div.CommentForm");

        // Change the form with for discussion editing
        if (FormBody.parents('#DiscussionForm').length > 0) {
            config.width = "915";
            config.theme_advanced_resizing_max_width = "915";
            config.theme_advanced_resizing_min_width = "915";
        }

        var editor = FormBody.tinymce(config);

        if (!$.editor) {
            // The first observed editor? then this must be the main editor
            $.mainEditor = editor;

            // tinyMCE can't handle multiple visible instances
            // (thanks to vanilla forum duplicating html ID values).
            // Thus, in the following we try to ensure that only one
            // tinyMCE instance is visible at any given time.

            $('.EditComment').livequery('click', function() {

                // User tries to edit a comment inline, remove current tinyMCE instance
                tinyMCE.execCommand('mceRemoveControl', false, 'Form_Body');

                if ($.editor == $.mainEditor) {
                    // If this was the main editor, then we should hide this area...
                    CommentForm.hide();
                } else if ($.editor.parents('div.Comment').find('#Form_CommentID').length == 0) {
                    // ... and if this was caused by the user clicking the same
                    // edit link twice, then we should restore the main editor.
                    // Since this handler is triggered after the old form has been removed,
                    // this can be checked by searching for a form at the current editor.
                    tinyMCE.execCommand('mceAddControl', false, 'Form_Body');
                    CommentForm.show();
                    $.editor = $.mainEditor;
                } else {
                    // Finally, if the user clicked on another edit link then we
                    // should remove the old editor instance.
                    $.editor.parents('div.Comment').find('div.Message').show();
                    $.editor.parents('div.CommentForm').remove();
                }
            });

            var initial = true;
            $('div.Comment').livequery(function() {
                if (initial) return;

                // New comment elements only appear if a comment was made
                // using the main comment box, or if we just finished editing
                // an existing comment. Thus, lets restore the comment area if
                // this is an instance of the latter.
                if ($.editor !== $.mainEditor) {
                    tinyMCE.execCommand('mceAddControl', false, 'Form_Body');
                    CommentForm.show();
                    $.editor = $.mainEditor;
                }
            });

            // If the cancel link is pressed then restore the main editor area
            $('ul.Discussion div.Comment a.Cancel').livequery('click', function() {
                tinyMCE.execCommand('mceAddControl', false, 'Form_Body');
                CommentForm.show();
                $.editor = $.mainEditor;
            });

            initial = false; // only run above function for new elements
        }

        $.editor = editor;

        jQuery(CommentForm).bind("clearCommentForm", {editor: editor}, function(e) {
            CommentForm.find("textarea").hide();
            e.data.editor.clearFields();
        });

        var isVisible = true;

        function toggleEditor() {
            if (isVisible) {
                tinyMCE.execCommand('mceRemoveControl', false, 'Form_Body');
                isVisible = false;
            } else {
                tinyMCE.execCommand('mceAddControl', false, 'Form_Body');
                isVisible = true;
            }
        }

        FormBody.parents('.TextBoxWrapper').after('<div class="TextBoxControls"><a href="javascript:void(0);">Toggle editor</a></div>');
        FormBody.parents('.TextBoxWrapper').next('.TextBoxControls').children('a').bind('click', {editor: editor}, function(e) {
            toggleEditor();
        });

        CommentForm.find('#Form_PostComment').bind('click', {editor: editor}, function(e) {
            if (!isVisible) {
                toggleEditor();
            }
        });
    });
});
