<?php

class BukuController
{
    public function __construct(private Buku $model) {}

    public function index(): void
    {
        $buku = $this->model->getAll();
        require __DIR__ . '/../views/buku/index.php';
    }

    public function create(): void
    {
        require __DIR__ . '/../views/buku/create.php';
    }

    public function store(): void
    {
        try {
            $data = $this->validateInput($_POST);
            $this->model->insert($data);

            header('Location: index.php?page=buku&status=created');
            exit;
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            exit(htmlspecialchars($e->getMessage()));
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(422);
                exit('ISBN sudah terdaftar. Gunakan ISBN yang berbeda.');
            }

            http_response_code(500);
            exit('Gagal menyimpan data buku.');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $buku = $this->model->findById($id);

        if (!$buku) {
            http_response_code(404);
            exit('Data buku tidak ditemukan.');
        }

        require __DIR__ . '/../views/buku/edit.php';
    }

    public function update(): void
    {
        try {
            $id = (int) ($_POST['id'] ?? 0);

            if ($id <= 0 || !$this->model->findById($id)) {
                http_response_code(404);
                exit('Data buku tidak ditemukan.');
            }

            $data = $this->validateInput($_POST);
            $this->model->update($id, $data);

            header('Location: index.php?page=buku&status=updated');
            exit;
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            exit(htmlspecialchars($e->getMessage()));
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(422);
                exit('ISBN sudah digunakan oleh buku lain.');
            }

            http_response_code(500);
            exit('Gagal memperbarui data buku.');
        }
    }

    public function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            http_response_code(422);
            exit('ID buku tidak valid.');
        }

        try {
            if (!$this->model->findById($id)) {
                http_response_code(404);
                exit('Data buku tidak ditemukan.');
            }

            $this->model->delete($id);

            header('Location: index.php?page=buku&status=deleted');
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Gagal menghapus data buku.');
        }
    }

    private function validateInput(array $input): array
    {
        $data = [
            'isbn' => trim($input['isbn'] ?? ''),
            'judul' => trim($input['judul'] ?? ''),
            'penulis' => trim($input['penulis'] ?? ''),
            'penerbit' => trim($input['penerbit'] ?? ''),
            'tahun_terbit' => trim((string) ($input['tahun_terbit'] ?? '')),
            'kategori' => trim($input['kategori'] ?? ''),
            'stok' => trim((string) ($input['stok'] ?? ''))
        ];

        foreach (['isbn', 'judul', 'penulis', 'penerbit',
                  'tahun_terbit', 'kategori', 'stok'] as $field) {
            if ($data[$field] === '') {
                throw new InvalidArgumentException(
                    'Semua kolom wajib diisi.'
                );
            }
        }

        if (strlen($data['isbn']) > 20 ||
            strlen($data['judul']) > 150 ||
            strlen($data['penulis']) > 100 ||
            strlen($data['penerbit']) > 100 ||
            strlen($data['kategori']) > 50) {
            throw new InvalidArgumentException(
                'Panjang salah satu kolom melebihi batas.'
            );
        }

        if (!ctype_digit($data['tahun_terbit']) ||
            (int) $data['tahun_terbit'] < 1901 ||
            (int) $data['tahun_terbit'] > (int) date('Y')) {
            throw new InvalidArgumentException(
                'Tahun terbit tidak valid.'
            );
        }

        if (!ctype_digit($data['stok'])) {
            throw new InvalidArgumentException(
                'Stok harus berupa bilangan bulat nol atau lebih.'
            );
        }

        if ((float) $data['stok'] > 4294967295) {
            throw new InvalidArgumentException(
                'Stok melebihi batas yang diizinkan.'
            );
        }

        return $data;
    }
}
