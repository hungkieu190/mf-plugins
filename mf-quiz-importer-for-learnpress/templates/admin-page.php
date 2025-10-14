<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <h1>MF Quiz Importer for LearnPress</h1>
  <p>Upload CSV/XLSX theo mẫu để import quiz & questions vào LearnPress. CSV nên dùng <code>UTF-8</code>.</p>

  <form method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('mfqi_import_nonce'); ?>
    <input type="hidden" name="mfqi_action" value="import" />

    <table class="form-table" role="presentation">
      <tr>
        <th scope="row"><label for="mfqi_file">File (CSV/XLSX)</label></th>
        <td><input type="file" id="mfqi_file" name="mfqi_file" required /></td>
      </tr>
      <tr>
        <th scope="row"><label for="delimiter">Delimiter cho cột answers/correct</label></th>
        <td>
          <input type="text" id="delimiter" name="delimiter" value=";" size="2" />
          <p class="description">Ví dụ: <code>React; Vue; Angular</code></p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="dry_run">Dry run</label></th>
        <td><label><input type="checkbox" id="dry_run" name="dry_run" value="1" /> Chạy thử (không ghi DB)</label></td>
      </tr>
    </table>

    <?php submit_button('Import Now'); ?>
  </form>

  <h2>CSV Columns</h2>
  <pre>course_id,section_name,quiz_title,question_type,question_text,answers,correct,mark</pre>
</div>
