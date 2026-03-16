<!DOCTYPE html>
<html>
<head>
    <title>Gestionnaire d'images</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .image-item {
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        .image-item:hover {
            border-color: #0d6efd;
            transform: scale(1.02);
        }
        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-3">
        <h5 class="mb-3">Sélectionner une image</h5>
        <div class="row g-2">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $image): ?>
                    <div class="col-3">
                        <div class="card image-item" onclick="selectImage('<?= $image['image'] ?>')">
                            <img src="<?= $image['thumb'] ?>" class="card-img-top" alt="">
                            <div class="card-body p-2">
                                <small class="text-muted"><?= basename($image['title']) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Aucune image trouvée</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function selectImage(url) {
        if (window.opener && window.opener.CKEDITOR) {
            var funcNum = window.location.search.match(/CKEditorFuncNum=(\d+)/);
            if (funcNum) {
                window.opener.CKEDITOR.tools.callFunction(funcNum[1], url);
                window.close();
            }
        }
    }
    </script>
</body>
</html>