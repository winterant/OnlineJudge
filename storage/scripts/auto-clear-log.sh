# Shrink big log file everyday.
while `sleep 86400` ; do
    access_size=$(du /var/log/nginx/access.log | awk '{printf($1)}')
    error_size=$(du /var/log/nginx/access.log | awk '{printf($1)}')
    if [ $access_size -gt 102400 ]; then
        echo "[auto clear log] nginx log file access.log is bigger than 100MB, shrink it."
        tail -50000 /var/log/nginx/access.log > /var/log/nginx/access.log
    fi
    if [ $error_size -gt 102400 ]; then
        echo "[auto clear log] nginx log file error.log is bigger than 100MB, shrink it."
        tail -50000 /var/log/nginx/error.log > /var/log/nginx/error.log
    fi
done
