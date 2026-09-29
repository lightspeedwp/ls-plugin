<?php
/**
 * Pattern: FAQ
 *
 * A heading followed by a Yoast FAQ block. Registered as `ls-plugin/faq` by
 * LS_Plugin\Template_Parts, only while Yoast SEO is active, and called from
 * the parts/faq.html template part.
 *
 * The block attributes and saved HTML are generated from the same array so
 * they always match, which keeps the Yoast block valid in the editor.
 *
 * @package LS_Plugin
 * @since   0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ls_plugin_faq_questions = [
	[
		'id'       => 'faq-question-1',
		'question' => __( 'What is your first frequently asked question?', 'ls-plugin' ),
		'answer'   => __( 'Replace this text with the answer to your first question.', 'ls-plugin' ),
	],
	[
		'id'       => 'faq-question-2',
		'question' => __( 'What is your second frequently asked question?', 'ls-plugin' ),
		'answer'   => __( 'Replace this text with the answer to your second question.', 'ls-plugin' ),
	],
	[
		'id'       => 'faq-question-3',
		'question' => __( 'What is your third frequently asked question?', 'ls-plugin' ),
		'answer'   => __( 'Replace this text with the answer to your third question.', 'ls-plugin' ),
	],
];

$ls_plugin_faq_attributes = [
	'questions' => array_map(
		function ( $item ) {
			return [
				'id'           => $item['id'],
				'question'     => esc_html( $item['question'] ),
				'answer'       => esc_html( $item['answer'] ),
				'jsonQuestion' => esc_html( $item['question'] ),
				'jsonAnswer'   => esc_html( $item['answer'] ),
				'images'       => [],
			];
		},
		$ls_plugin_faq_questions
	),
];
?>
<!-- wp:group {"metadata":{"name":"<?php echo esc_attr__( 'FAQ', 'ls-plugin' ); ?>"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html__( 'Frequently asked questions', 'ls-plugin' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:yoast/faq-block <?php echo serialize_block_attributes( $ls_plugin_faq_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- serialize_block_attributes() escapes for block comment delimiters. ?> -->
<div class="schema-faq wp-block-yoast-faq-block"><?php foreach ( $ls_plugin_faq_attributes['questions'] as $ls_plugin_faq_item ) : ?><div class="schema-faq-section" id="<?php echo esc_attr( $ls_plugin_faq_item['id'] ); ?>"><strong class="schema-faq-question"><?php echo $ls_plugin_faq_item['question']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></strong> <p class="schema-faq-answer"><?php echo $ls_plugin_faq_item['answer']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></p> </div> <?php endforeach; ?></div>
<!-- /wp:yoast/faq-block --></div>
<!-- /wp:group -->
