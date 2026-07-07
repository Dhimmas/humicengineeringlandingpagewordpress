<?php
get_header();
echo do_shortcode('[humic_header active="home"]');
?>
<main>
    <?php echo do_shortcode('[humic_hero]'); ?>
    <?php echo do_shortcode('[humic_stats_bar]'); ?>
    <?php echo do_shortcode('[humic_about]'); ?>
    <?php echo do_shortcode('[humic_research_areas]'); ?>
    
    <section id="news" class="news">
      <div class="container">
        <div class="section-hdr">
          <div>
            <span class="eyebrow">Stay Updated</span>
            <h2 class="section-title">Latest News</h2>
          </div>
          <a href="<?php echo do_shortcode('[humic_news_url]'); ?>" class="link-arr">View All <i class="fa-solid fa-arrow-up-right"></i></a>
        </div>
        <?php echo do_shortcode('[humic_news]'); ?>
      </div>
    </section>

    <section id="events" class="events-home">
      <div class="container">
        <div class="section-hdr">
          <div>
            <span class="eyebrow">What's On</span>
            <h2 class="section-title">Upcoming Events</h2>
          </div>
          <a href="<?php echo do_shortcode('[humic_events_url]'); ?>" class="link-arr">View All Events <i class="fa-solid fa-arrow-up-right"></i></a>
        </div>
        <?php echo do_shortcode('[humic_events_home]'); ?>
      </div>
    </section>

    <section id="partners" class="partners">
      <div class="container">
        <div class="section-hdr section-hdr-center">
          <div class="section-hdr-inner">
            <span class="eyebrow">Collaborations</span>
            <h2 class="section-title">Our Partners</h2>
          </div>
        </div>
        <?php echo do_shortcode('[humic_partners]'); ?>
      </div>
    </section>

    <section id="contact" class="contact">
      <div class="container">
        <div class="section-hdr section-hdr-center">
          <div class="section-hdr-inner">
            <span class="eyebrow">Get In Touch</span>
            <h2 class="section-title">Contact Us</h2>
          </div>
        </div>
        <div class="contact-boxes contact-boxes-duo">
          <?php echo do_shortcode('[humic_contact_office]'); ?>
          <?php echo do_shortcode('[humic_contact_keluhan]'); ?>
        </div>
      </div>
    </section>
</main>
<?php
echo do_shortcode('[humic_footer]');
get_footer();
?>