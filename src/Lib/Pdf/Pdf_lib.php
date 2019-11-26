<?php

namespace KarmaFW\Lib\Pdf;

use \Mpdf\Mpdf;


class Pdf_lib
{

	protected $tocPreHTML = '<div class="container"><h1>Table des matières</h1><br /><br />';
	protected $tocPostHTML = '';
	protected $tocFont = 'Roboto';
	protected $subject = 'Document';
	protected $author = '';
	protected $creator = '';


	protected static function getFooter()
	{
		return 'KarmaCRM by <a href="http://www.karma-solutions.fr/" class="text-muted">Karma Solutions</a> © 2019 &nbsp; - &nbsp; page {PAGENO}/{nb} &nbsp;';
	}



	public static function buildPDF($pages_html, $pdf_options=array(), $output_inline_filename=true, $output_file=null)
	{
		// Buid PDF using MPDF

		$mpdf = new Mpdf([
			//'orientation' => 'L', 
			//'format' => [210, 297], 
			'format' => ! empty($pdf_options['format']) ? $pdf_options['format'] : 'A4-L', 
			'tempDir' => ! empty($pdf_options['tempDir']) ? $pdf_options['tempDir'] : '/tmp/mpdf',
			'margin_left' => 0,
			'margin_right' => 0,
			'margin_top' => 0,
			'margin_bottom' => 0,
			'margin_header' => 0,
			'margin_footer' => 0,
		]);

		//$mpdf->showImageErrors = true;

		if (! empty($pdf_options['base_href'])) {
			$mpdf->setBasePath($pdf_options['base_href']);
		}

		$mpdf->SetDisplayMode(! empty($pdf_options['display_mode']) ? $pdf_options['display_mode'] : 'fullpage');
		$mpdf->SetFontSize(! empty($pdf_options['font_size']) ? $pdf_options['font_size'] : 6);
		//$mpdf->useOnlyCoreFonts = true;
		//$mpdf->shrink_tables_to_fit = 1;

		//$mpdf->SetHTMLHeader("Page HTML Head");
		//$mpdf->SetHTMLFooter("Page HTML Foot");

		//$mpdf->SetHeader("Page Head");

		
		/*
		$mpdf->h2toc = array(
		    'H1' => 0, 
		    'H2' => 1, 
		    'H3' => 2
		);
		*/
		


		$page_idx = 0;
		foreach ($pages_html as $page_name => $page) {
			$page_idx++;

			$toc_level = isset($page['toc_level']) ? $page['toc_level'] : 0;
			$toc_title = isset($page['toc_title']) ? $page['toc_title'] : $page_name;

			$mpdf->AddPage();

			if ($page_idx == 2) {
				// on met le footer uniquement à partir de la 2eme page
				$mpdf->SetFooter( self::getFooter() );
			}


			if ($page_idx > 1) {
				$mpdf->TOC_Entry($toc_title, $toc_level);
			}


			if ($page_idx == 2) {
				// on insere la table des matieres juste apres la 1ere page
				$mpdf->TOCpagebreakByArray( [
					'tocfont' => static::$tocFont,
					'toc_mgl' => 8,
					'toc_mgr' => 8,
					'toc-preHTML' => static::$tocPreHTML, 
					'toc-postHTML' => static::$tocPostHTML, 
					'links' => true,
				] );
			}

			if (! empty($page['content'])) {
				@$mpdf->WriteHTML($page['content']);
			}

			if (! empty($page['footer'])) {
				@$mpdf->setHTMLFooter($page['footer']);
			}
		}

		//$mpdf->SetTitle("Rapport de crawl Karma Crawler"); // utiliser strcode2utf() si besoin
		$mpdf->SetSubject(static::$subject);
		$mpdf->SetAuthor(static::$author);
		$mpdf->SetCreator(static::$creator);
		


		// Save file
		if ($output_file) {
			$mpdf->Output($output_file, 'F');
		}

		// output file inline
		if ($output_inline_filename) {
			$pdf_filename = basename($output_inline_filename);
			$mpdf->Output($pdf_filename, 'I');
		}

	}

}
