import os

def scan_and_save():
    output_file = "scan_result.txt"

    # папки, которые игнорируем
    ignore_dirs = {".git", "__pycache__", ".idea", ".vscode", "node_modules"}
    # расширения, которые игнорируем
    ignore_ext = {
        ".png", ".jpg", ".jpeg", ".gif", ".bmp",
        ".ico", ".exe", ".dll", ".so", ".bin",
        ".pack", ".idx", ".sample"
    }

    with open(output_file, "w", encoding="utf-8") as out:
        for root, dirs, files in os.walk(os.getcwd()):
            # фильтруем директории
            dirs[:] = [d for d in dirs if d not in ignore_dirs]

            for file in files:
                filepath = os.path.join(root, file)

                # пропускаем ненужные расширения
                if any(file.lower().endswith(ext) for ext in ignore_ext):
                    continue

                # не включаем сам результат
                if file == output_file:
                    continue

                out.write(f"Файл: {file}\n")
                out.write(f"Путь: {filepath}\n")
                out.write("Содержимое:\n")

                try:
                    with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
                        out.write(f.read())
                except Exception as e:
                    out.write(f"[Ошибка чтения: {e}]")

                out.write("\n" + "-" * 80 + "\n\n")

    print(f"Готово. Результаты сохранены в {output_file}")

if __name__ == "__main__":
    scan_and_save()
