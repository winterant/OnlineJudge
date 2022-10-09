const ck_config = {
    removePlugins: ['FontBackgroundColor'],
    // language: 'zh-cn',
    ckfinder: {
        uploadUrl:''
    },
    image: {
        // resizeUnit: 'rem',
        resizeOptions: [
            {
                name: 'resizeImage:S1',
                value: '20',
                icon: 'small'
            },
            {
                name: 'resizeImage:S2',
                value: '50',
                icon: 'small'
            },
            {
                name: 'resizeImage:S3',
                value: '75',
                icon: 'medium'
            },
            {
                name: 'resizeImage:original',
                value: null,
                icon: 'original'
            },
            {
                name: 'resizeImage:L1',
                value: '150',
                icon: 'large'
            },
            {
                name: 'resizeImage:L2',
                value: '200',
                icon: 'large'
            }
        ],
        toolbar: [
            'imageTextAlternative',
            'toggleImageCaption',
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            'resizeImage:original',
            'resizeImage:S1',
            'resizeImage:S2',
            'resizeImage:S3',
            'resizeImage:L1',
            'resizeImage:L2',
        ]
    },
    // table: {
    //     contentToolbar: [
    //         'tableColumn',
    //         'tableRow',
    //         'mergeTableCells'
    //     ]
    // },
    codeBlock: {
        languages: [
            { language: 'cpp', label: 'C++', class: 'cpp'}, // The default language.
            // { language: 'c', label: 'C' },  // 默认class为language-c
            { language: 'java', label: 'Java' },
            { language: 'python', label: 'Python' },
            { language: 'plaintext', label: 'Plain text' },
        ]
    },
    // licenseKey: '',
}
