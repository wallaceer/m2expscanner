<?php
$m2Path = __DIR__ . DIRECTORY_SEPARATOR	.	'app/etc/env.php';
//if(!file_exists($m2Path)) exit("M2 is necessary! Check your installation please!");
?>
<link  rel="stylesheet" type="text/css"  media="all" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
<link  rel="stylesheet" type="text/css"  media="all" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" />
<link  rel="stylesheet" type="text/css"  media="all" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" />

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="https://webrtc.github.io/adapter/adapter-latest.js" type="text/javascript"></script>
<script type="text/javascript" src="assets/js/quagga.min.js"></script>
<script type="text/javascript" src="https://github.com/serratus/quaggaJS/blob/master/example/live_w_locator.js"></script>

<input id="scanner_input" class="form-control" placeholder="Click the button to scan an EAN..." type="text" style="display: none;"/>
<button class="btn btn-default" type="button" data-toggle="modal" data-target="#livestream_scanner">
    <!--i class="fa fa-barcode"></i-->
    <img src="assets/images/barcode-scanner.png" />
</button>
<div id="result"></div>

<div class="modal" id="livestream_scanner">
    <video playsinline autoplay="true"></video>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Barcode Scanner</h4>
            </div>
            <div class="modal-body" style="position: static">
                <div id="interactive" class="viewport"></div>
                <div class="error"></div>
            </div>
            <div class="modal-footer">
                <!-- label class="btn btn-default pull-left">
                    <i class="fa fa-camera"></i> Use camera app
                    <input type="file" accept="image/*;capture=camera" capture="camera" class="hidden" />
                </label -->
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<style>
    #interactive.viewport {position: relative; width: 100%; height: auto; overflow: hidden; text-align: center;}
    #interactive.viewport > canvas, #interactive.viewport > video {max-width: 100%;width: 100%;}
    canvas.drawing, canvas.drawingBuffer {position: absolute; left: 0; top: 0;}
</style>
<script type="text/javascript">
    $(function() {

        $('#scanner_input').val();

        // Create the QuaggaJS config object for the live stream
        var liveStreamConfig = {
            inputStream: {
                type : "LiveStream",
                constraints: {
                    minWidth: {min:640},
                    minHeight: {min:480},
                    aspectRatio: {min: 1, max: 100},
                    facingMode: "environment" // or "user" for the front camera
                }
            },
            locator: {
                patchSize: "medium",
                halfSample: true
            },
            numOfWorkers: (navigator.hardwareConcurrency ? navigator.hardwareConcurrency : 4),
            decoder: {
                "readers":[
                    {"format":"ean_reader","config":{}}
                ]
            },
            locate: true
        };
        // The fallback to the file API requires a different inputStream option.
        // The rest is the same
        var fileConfig = $.extend(
            {},
            liveStreamConfig,
            {
                inputStream: {
                    size: 800
                }
            }
        );
        // Start the live stream scanner when the modal opens
        $('#livestream_scanner').on('shown.bs.modal', function (e) {
            $('#result').empty();
            Quagga.init(
                liveStreamConfig,
                function(err) {
                    if (err) {
                        $('#livestream_scanner .modal-body .error').html('<div class="alert alert-danger"><strong><i class="fa fa-exclamation-triangle"></i> '+err.name+'</strong>: '+err.message+'</div>');
                        Quagga.stop();
                        return;
                    }
                    Quagga.start();
                }
            );
        });

        // Make sure, QuaggaJS draws frames an lines around possible
        // barcodes on the live stream
        Quagga.onProcessed(function(result) {
            var drawingCtx = Quagga.canvas.ctx.overlay,
                drawingCanvas = Quagga.canvas.dom.overlay;

            if (result) {
                if (result.boxes) {
                    drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));
                    result.boxes.filter(function (box) {
                        return box !== result.box;
                    }).forEach(function (box) {
                        Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {color: "green", lineWidth: 2});
                    });
                }

                if (result.box) {
                    Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {color: "#00F", lineWidth: 2});
                }

                if (result.codeResult && result.codeResult.code) {
                    Quagga.ImageDebug.drawPath(result.line, {x: 'x', y: 'y'}, drawingCtx, {color: 'red', lineWidth: 3});
                }
            }
        });

        // Once a barcode had been read successfully, stop quagga and
        // close the modal after a second to let the user notice where
        // the barcode had actually been found.
        Quagga.onDetected(function(result) {
            if (result.codeResult.code){
                $('#scanner_input').val(result.codeResult.code);
                Quagga.stop();
                setTimeout(function(){ $('#livestream_scanner').modal('hide'); }, 1000);
                getPrice();
            }
        });

        // Stop quagga in any case, when the modal is closed
        $('#livestream_scanner').on('hide.bs.modal', function(){
            if (Quagga){
                Quagga.stop();
            }
        });

        // Call Quagga.decodeSingle() for every file selected in the
        // file input
        $("#livestream_scanner input:file").on("change", function(e) {
            if (e.target.files && e.target.files.length) {
                Quagga.decodeSingle($.extend({}, fileConfig, {src: URL.createObjectURL(e.target.files[0])}), function(result) {alert(result.codeResult.code);});
            }
        });


        function getPrice(){
            var sku = $('#scanner_input').val();
            $.ajax({
                type: "POST",
                url: 'php/getProduct.php',
                data: "sku="+sku,
                success: function(response){
                    $("#result").append(response);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    $("#result").append(xhr.status);
                    $("#result").append(thrownError);
                }
            });
        }


    });

</script>