# Shrink big log file everyday.

current_dir=$(cd $(dirname $0); pwd)
cd $current_dir

while `sleep 86400` ; do
  for file in $(ls $current_dir)
  do
    if [ -f $file ] && [[ $file == *".log" ]]; then
      file_size=$(du -s $file | awk '{printf($1)}')  # KB
      if [ $file_size -gt 10240 ]; then  # >10MB
        echo "[auto clear log] ${current_dir}/${file} is bigger than 10MB, shrink it."
        tail -1000 $file > $file.bak
        cat $file.bak > $file
        rm $file.bak
      fi
    fi
  done
done
