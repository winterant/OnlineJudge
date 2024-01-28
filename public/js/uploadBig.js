function uploadBig(obj) {
    const args = {
        url: obj.url,        //必须,相当于form的action
        _token: obj._token,  //必须，laravel token：'{{csrf_token()}}'
        files: obj.files,    //必须，上传的文件列表
        data: obj.data,      //可选，除files外的其他数据
        blockSize: 1024 * (obj.blockSizeInKB !== undefined ? obj.blockSizeInKB : 900),  //可选，每块的大小，默认900KB
        before: obj.before,       //可选，上传前执行函数
        uploading: obj.uploading, //可选，上传中执行函数
        success: obj.success,     //可选，上传成功执行函数
        error: obj.error,         //可选，上传出错执行函数
    };

    function dfs_ajax(index = 0, start = 0) {
        var formData = new FormData();
        formData.append('filename', args.files[index].name);  //文件原始名
        formData.append('file_index', index)                  // 文件编号 0 ~ n-1
        formData.append('num_files', args.files.length)       // 文件个数
        formData.append('block_index', Math.round(start / args.blockSize));  //块号 0 ~ n-1
        formData.append('num_blocks', Math.max(1, Math.ceil(args.files[index].size / args.blockSize)));//块数
        formData.append('block', args.files[index].slice(start, start + args.blockSize)); //文件块,实际文件内容

        if (args.data !== undefined) {
            for (let key of Object.keys(args.data)) //除文件外的附加数据
                formData.append(key, args.data[key]);
        }

        $.ajax({
            headers: {'X-CSRF-TOKEN': args._token},
            url: args.url,
            type: 'post',
            data: formData,
            processData: false,
            contentType: false,
            success: function (ret) {
                if (index === args.files.length - 1 && start + args.blockSize >= args.files[index].size)//最后一个上传完毕
                {
                    //上传成功...回调函数[文件总数，控制器返回值]
                    if (args.success !== undefined)
                        args.success(args.files.length, ret);
                } else {
                    //上传中...回调函数，参数[文件总数，当前第几个，当前文件已上传大小KB]
                    if (args.uploading !== undefined)
                        args.uploading(args.files.length, index + 1, (start + args.blockSize), args.files[index].size);

                    if (start + args.blockSize >= args.files[index].size)//跳到下一个文件
                        dfs_ajax(index + 1, 0);//递归
                    else
                        dfs_ajax(index, start + args.blockSize);//递归
                }

            },
            error: function (xhr, status, err) {
                if (args.error !== undefined)
                    args.error(xhr, status, err);
            }
        });
    }

    //上传开始前回调函数，参数[文件数量，合计大小KB]
    if (args.before !== undefined) {
        let total_size = 0;
        for (const file_temp of args.files)
            total_size += file_temp.size;
        args.before(args.files.length, total_size);
    }

    dfs_ajax(); //递归按顺序开始执行ajax
}
