<div class="m-b-1 m-t-1">
    <fieldset class="form-group">
        {* Affichage des alertes *}
        <div class="alert alert-success" role="alert" style="display: none;">
            {l s='The video has been uploaded with success' mod='e5_animatedvideo'}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="alert alert-danger" role="alert" style="display: none;">
            {l s='An error occured during the upload' mod='e5_animatedvideo'}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        {* Affichage du champ Image *}
        <div class="col-lg-4 col-xl-4 mx-auto">
            <div id="video_name_thumbnail">
                {* Si une video existe déjà sur le produit *}
                {if isset($video_name) && $video_name !=""}
                    <div style="padding:10px; margin-bottom: 30px; margin-top: 30px; aspect-ratio: 1;">
                        <label
                            class="form-control-label">{l s='The current video of the product' mod='e5_animatedvideo'}</label><br />
                        <video src="{$file_dir}{$video_name}" style="width: 100%;height: 100%;object-fit: cover" loop
                            autoplay></video><br />
                        <button type="button" class="btn btn-outline-danger sensitive open video_name_delete">
                            <i class="material-icons">delete</i>{l s='Delete this video' mod='e5_animatedvideo'}
                        </button>
                    </div>

                {/if}
            </div>
        </div>
        <div id="product-images-container" class="mb-4">
            <div id="product-images-dropzone" class="panel dropzone col-md-12">
                <div id="product-images-dropzone-error" class="text-danger"></div>
                <div id="video_name_uploader" class="dz-default dz-message openfilemanager">
                    <i class="material-icons">movie_creation</i><br />
                    {l s='Drop video here' mod='e5_animatedvideo'}<br />
                    <a>{l s='or select files' mod='e5_animatedvideo'}</a><br />
                    <small>
                        {l s='MP4, MOV, WEBM format' mod='e5_animatedvideo'}
                    </small>
                </div>
            </div>
        </div>
        <script>
            $(function() {
                //Gestion de l'upload via la librairie DropZone
                Dropzone.autoDiscover = false;
                var customFieldImageDropzone = $('#video_name_uploader').dropzone({
                    url: '{$moduleLink}&id_product={$id_product}&uploadProductImage=1&field_name=video_name',
                    paramName: "file", //Nom du champs à envoyer
                    previewTemplate: document.querySelector('#video_name_dropzone_template')
                        .innerHTML,
                    acceptedFiles: '.mp4,.mov,.webm',
                    disablePreview: true,
                    renameFile: function(file) {
                        return new Date().getTime() + '_' + file.name;
                    },
                    success: function(file) {
                        //Afficher l'alerte
                        $('.alert-success').show();
                        //Affichage de la nouvelle video dans l'emplacement
                        $('#video_name_thumbnail').html('').html(file.previewElement);
                        $('#video_name_thumbnail video').attr('src', '{$file_dir}' + file.upload.filename);
                    },
                    error: function(file, errorMessage) {
                        $('.alert-danger').show();
                    },
                });

                //Gestion de la suppression
                $('#video_name_thumbnail').on('click', '.video_name_delete', function() {
                    $.ajax({
                        method: 'post',
                        url : '{$moduleLink}&id_product={$id_product}&deleteProductImage=1&field_name=video_name',
                        success: function(msg) {
                            console.log(msg);
                            alert('{l s='File delete with success' mod='e5_animatedvideo'}');
                            $('#video_name_thumbnail').html('');
                        }
                    });
                    return false;
                });
            });
        </script>

        <div id="video_name_dropzone_template" style="display:none">
            <div style="padding:10px; margin-bottom: 30px; margin-top: 30px;">
                <div class="dz-details" style="aspect-ratio: 1;">
                    <video src="{$file_dir}{$video_name}" style="width: 100%;height: 100%;object-fit: cover" loop
                        autoplay></video><br />
                </div>
                <button type="button" class="btn btn-outline-danger sensitive open video_name_delete">
                    <i class="material-icons">delete</i>{l s='Delete this video' mod='e5_animatedvideo'}
                </button>
            </div>
        </div>

    </fieldset>

    <div class="clearfix"></div>
</div>