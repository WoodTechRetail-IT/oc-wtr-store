<?php
Header('Content-type: text/xml');
$xw = xmlwriter_open_memory();
xmlwriter_set_indent($xw, 1);
$res = xmlwriter_set_indent_string($xw, ' ');
xmlwriter_start_document($xw, '1.0', 'UTF-8');
// Первый элемент
xmlwriter_start_element($xw, 'tag1');
// Атрибут 'att1' для элемента 'tag1'
xmlwriter_start_attribute($xw, 'att1');
xmlwriter_text($xw, 'valueofatt1');
xmlwriter_end_attribute($xw);
xmlwriter_write_comment($xw, 'this is a comment.');
// Создаём дочерний элемент
xmlwriter_start_element($xw, 'tag11');
xmlwriter_text($xw, 'This is a sample text, ä');
xmlwriter_end_element($xw); // tag11
xmlwriter_end_element($xw); // tag1
// CDATA
xmlwriter_start_element($xw, 'testc');
xmlwriter_write_cdata($xw, "This is cdata content");
xmlwriter_end_element($xw); // testc
xmlwriter_start_element($xw, 'testc');
xmlwriter_start_cdata($xw);
xmlwriter_text($xw, "test cdata2");
xmlwriter_end_cdata($xw);
xmlwriter_end_element($xw); // testc
// Инструкции по обработке
xmlwriter_start_pi($xw, 'php');
xmlwriter_text($xw, '$foo=2;echo $foo;');
xmlwriter_end_pi($xw);
xmlwriter_end_document($xw);
echo xmlwriter_output_memory($xw);

?>