tinymce.init({
    selector: 'textarea:not(.no-tinymce)',
    plugins: [
        "advlist autolink lists link image charmap print preview anchor",
        "searchreplace visualblocks code fullscreen",
        "insertdatetime media table contextmenu paste"
    ],
    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
    mode : "textareas",
    force_br_newlines: false,
    force_p_newlines: false,
    forced_root_block: 'p',
    content_css: '/css/tinymce-content.css',
    height : "500",
    extended_valid_elements : 'a[ui-rel-sref|ui-sref|class|href|target]'
});
