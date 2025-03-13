<?php

function generatePagination($currentPage, $totalPages, $url)
{
    $pagination = '<div class="pagination">';

    if ($currentPage > 1) {
        $pagination .= '<a href="' . $url . '?page=' . ($currentPage - 1) . '" class="button">Previous</a>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = ($i == $currentPage) ? 'active' : '';
        $pagination .= '<a href="' . $url . '?page=' . $i . '" class="button ' . $activeClass . '">' . $i . '</a>';
    }

    if ($currentPage < $totalPages) {
        $pagination .= '<a href="' . $url . '?page=' . ($currentPage + 1) . '" class="button">Next</a>';
    }

    $pagination .= '</div>';
    return $pagination;
}