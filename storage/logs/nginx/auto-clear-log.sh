# Shrink big log file everyday.
while `sleep 86400` ; do
    log_dir=/app/storage/logs/nginx
    access_size=$(du $log_dir/access.log | awk '{printf($1)}')
    if [ $access_size -gt 102400 ]; then
        echo "[auto clear log] nginx log file access.log is bigger than 100MB, shrink it."
        tail -50000 $log_dir/access.log > $log_dir/access.log
    fi
    error_size=$(du $log_dir/error.log | awk '{printf($1)}')
    if [ $error_size -gt 102400 ]; then
        echo "[auto clear log] nginx log file error.log is bigger than 100MB, shrink it."
        tail -50000 $log_dir/error.log > $log_dir/error.log
    fi
done
