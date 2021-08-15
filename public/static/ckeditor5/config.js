const ck_config = {
    toolbar: {
        items: [
            'heading',
            '|',
            'fontFamily',
            'fontSize',
            'fontColor',
            'fontBackgroundColor',
            'highlight',
            '|',
            'bold',
            'italic',
            'link',
            'bulletedList',
            'numberedList',
            '|',
            'alignment',
            'outdent',
            'indent',
            '|',
            'horizontalLine',
            'code',
            'codeBlock',
            '|',
            'blockQuote',
            'insertTable',
            'imageUpload',
            'undo',
            'redo'
        ]
    },
    language: 'zh-cn',
    removePlugins: ['Title'],  //取消自动填充初始标题
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
