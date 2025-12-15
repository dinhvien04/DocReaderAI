<?php
/**
 * History Helper Functions
 * Format and display audio history with position status
 */

/**
 * Format seconds to MM:SS format
 * 
 * @param int $seconds Total seconds
 * @return string Formatted time (e.g., "01:15")
 */
function formatSecondsToTime($seconds) {
    $seconds = (int)$seconds;
    $minutes = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf('%02d:%02d', $minutes, $secs);
}

/**
 * Get position status badge HTML
 * 
 * @param int $position Position in seconds
 * @return string HTML badge
 */
function getPositionBadge($position) {
    $position = (int)$position;
    
    if ($position > 0) {
        $formattedTime = formatSecondsToTime($position);
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                    </svg>
                    Đã nghe đến ' . $formattedTime . '
                </span>';
    } else {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Chưa nghe
                </span>';
    }
}

/**
 * Truncate text with ellipsis
 * 
 * @param string $text Text to truncate
 * @param int $maxLength Maximum length
 * @return string Truncated text
 */
function truncateText($text, $maxLength = 60) {
    if (mb_strlen($text) <= $maxLength) {
        return $text;
    }
    return mb_substr($text, 0, $maxLength) . '...';
}

/**
 * Format date for display
 * 
 * @param string $dateString Date string
 * @return string Formatted date
 */
function formatHistoryDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('d/m/Y H:i');
}

/**
 * Render a single history row
 * 
 * @param array $item History item data
 * @return string HTML row
 */
function renderHistoryRow($item) {
    $id = (int)$item['id'];
    $text = htmlspecialchars($item['text'] ?? 'Không có văn bản');
    $voice = htmlspecialchars($item['voice'] ?? 'Unknown');
    $audioUrl = htmlspecialchars($item['audio_url'] ?? '');
    $position = (int)($item['position'] ?? 0);
    $createdAt = formatHistoryDate($item['created_at']);
    $truncatedText = htmlspecialchars(truncateText($item['text'] ?? '', 60));
    
    return '
    <tr class="audio-history-row cursor-pointer hover:bg-gray-50 hover:shadow-sm border-b transition-all duration-150"
        data-audio-id="' . $id . '"
        data-audio-url="' . $audioUrl . '"
        data-audio-text="' . $text . '"
        data-audio-voice="' . $voice . '"
        data-audio-date="' . htmlspecialchars($item['created_at']) . '"
        data-audio-position="' . $position . '">
        <td class="px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">' . $truncatedText . '</p>
                    <p class="text-xs text-gray-500 mt-0.5">Click để xem chi tiết</p>
                </div>
            </div>
        </td>
        <td class="px-6 py-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd" />
                </svg>
                ' . $voice . '
            </span>
        </td>
        <td class="px-6 py-4">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
                ' . $createdAt . '
            </div>
        </td>
    </tr>';
}

/**
 * Render history table
 * 
 * @param array $items Array of history items
 * @return string HTML table body content
 */
function renderHistoryTable($items) {
    if (empty($items)) {
        return '
        <tr>
            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                <div class="text-4xl mb-2">📭</div>
                <p>Chưa có lịch sử hoạt động nào</p>
            </td>
        </tr>';
    }
    
    $html = '';
    foreach ($items as $item) {
        $html .= renderHistoryRow($item);
    }
    return $html;
}
