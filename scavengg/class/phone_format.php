<?php

function phone_format($data) {
    return "+1 (".substr($data, 0, 3).") ".substr($data, 3, 3)."-".substr($data,6)."";
}

?>