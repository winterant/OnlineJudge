const ck_config = {
    toolbar: {
        items: [
            'heading',
            '|',
            'fontColor',
            'highlight',
            'horizontalLine',
            'bold',
            'italic',
            '|',
            'code',
            'codeBlock',
            'blockQuote',
            'link',
            '|',
            'alignment',
            'outdent',
            'indent',
            'bulletedList',
            'numberedList',
            '|',
            'insertTable',
            'imageUpload',
            'mediaEmbed',
            '|',
            'removeFormat',
            'undo',
            'redo'
        ]
    },
    // removePlugins: ['TextTransformation'],
    language: 'zh-cn',
    ckfinder: {
        uploadUrl:''
    },
    image: {
        toolbar: [
            'imageTextAlternative',
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side'
        ]
    },
    table: {
        contentToolbar: [
            'tableColumn',
            'tableRow',
            'mergeTableCells'
        ]
    },
    codeBlock: {
        languages: [
            { language: 'cpp', label: 'C++', class: 'cpp'}, // The default language.
            { language: 'c', label: 'C' },  // 默认class为language-c
            { language: 'java', label: 'Java' },
            { language: 'python', label: 'Python' },
            { language: 'plaintext', label: 'Plain text' },
        ]
    },
    licenseKey: '',
}
