<?php

/* Quit */
defined('ABSPATH') || exit;

if(function_exists('fau_initoptions')) {
    $options = fau_initoptions();
} else {
    $options = array();
}

$breadcrumb = '';
if (isset($options['breadcrumb_root'])) {
    if ($options['breadcrumb_withtitle']) {
        $breadcrumb .= '<h3 class="breadcrumb_sitetitle" role="presentation">'.get_bloginfo('title').'</h3>';
        $breadcrumb .= "\n";
    }
    $breadcrumb .= '<nav aria-labelledby="bc-title" class="breadcrumbs">'; 
    $breadcrumb .= '<h4 class="screen-reader-text" id="bc-title">'.__('Sie befinden sich hier:','fau').'</h4>';
    $breadcrumb .= '<a data-wpel-link="internal" href="' . site_url('/') . '">' . $options['breadcrumb_root'] . '</a>';
}

/* Captcha */

$matching_numbers = array(
    'eins'  => 1,
    'zwei'  => 2,
    'drei'  => 3,
    'vier'  => 4,
    'fünf'  => 5,
    'sechs' => 6,
    'sieben'=> 7,
    'acht'  => 8,
    'neun'  => 9
);

$operator = array(
    '+' => 'plus',
    '*' => 'mal'
);

$min_number = 1;
$max_number = 9;

$random_number1 = mt_rand($min_number, $max_number);
$random_number2 = mt_rand($min_number, $max_number);
$random_operator = array_rand($operator, 1);

$figure = array_search($random_number2, $matching_numbers);

$op = $operator[$random_operator[0]];

$solution = $random_number1 . ' ' . $operator[$random_operator[0]] . ' ' . $figure;

$flipped = array_flip($matching_numbers);

$opflipped = array_search($op, $operator);

switch ($op) {
  case 'plus':
    $output = $random_number1 + $random_number2;
    break;
  case 'minus':
    $output = $random_number1 - $random_number2;
    break;
  case 'mal':
    $output = $random_number1 * $random_number2;
    break;
}

/*if(!isset($_POST['submit'])) {
    $out = md5($output);
}*/

/*if( get_option('captcha') === false) {
    add_option('captcha', md5($output));
}*/

/* Check Custom Posttype - wcag */
global $post;

$args = array( 'post_type' => 'wcag' );

$loop = new WP_Query( $args );

get_header(); ?>

    <section id="hero" class="hero-small">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <?php echo $breadcrumb; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <h1>WCAG 2.0 AA Prüfung</h1>
                </div>
            </div>
        </div>
    </section>

    <div id="content">
        <div class="container">

            <div class="row">
                <div class="col-xs-12">
                    <main>                        
                        <h2>Prüfungsergebnisse gemäß WCAG 2.0 AA</h2>
                        <p>Diese Webseite wurde gemäß den Konformitätsbedingungen der WCAG geprüft.</p>
                        <h3>Sind die Konformitätskriterien derzeit erfüllt?</h3><br />
                        <?php  
                        while ( $loop->have_posts() ) : $loop->the_post();
                            $complete = get_post_meta($post->ID, 'wcag_complete', true);
                            if($complete == 1) { ?>
                                <p class="wcag-pass">Die Kriterien werden erfüllt.</p>
                            <?php } else { ?>
                                <p class="wcag-fail">Die Kriterien werden nicht erfüllt.</p>
                                <p style="margin-top:20px;margin-bottom:20px"><strong>Begründung:</strong></p>
                                <?php the_content();
                             } 
                        endwhile; ?>
                            <?php 
                            if (isset($_POST['submit'])){
                                /*$cap = md5($_POST['captcha']);
                                /*echo $cap;
                                echo get_option('captcha');
                                delete_option('captcha');*/
                                /*if($cap === $out) {
                                    echo 'ok';
                                }else {
                                    echo 'nicht ok';
                                }*/
                                //echo $_POST['captcha'];
                                ?> 
                                   <p>Vielen Dank für Ihr Feedback! Wir werden uns umgehend bei Ihnen melden.</p> 
                                <?php
                            } else {;
                            ?>
                                <br /><h2>Probleme bei der Bedienung der Seite?</h2>
                                <p>Sollten Sie Probleme bei der Bedingung der Webseite haben, füllen Sie bitte das Feedback-Formular aus!</p><br />


                                <form method="post" id="captchaform">
                                    <p>
                                        <label for="feedback">Ihr Feedback</label>
                                        <textarea id="feeadback" name="feedback" cols="150" rows="10"></textarea>
                                    </p>
                                    <p>
                                        <label for="check">Lösen Sie folgende Aufgabe:</label></p>
                                        <p><?php echo $solution . ' = ' ?> <input type="text" name="captcha" id="check" /></p>
                                </form>
                                 <input type="submit" name="submit" form="captchaform" value="Jetzt abschicken">
                                 <?php 
                                   echo get_option('captcha');
                                 //echo $out;
                            }
                        ?>
                    </main>
                </div>

            </div>

        </div>
    </div>

<?php get_footer(); ?>
