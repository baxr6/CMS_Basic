        </div> <!-- end column-center -->

        <div class="column-right">
            <?php render_blocks('right'); ?>
        </div>
    </div> <!-- end page-columns -->

    <div class="container">
        <?php render_blocks('footer'); ?>
        <footer>
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($settings['site_name']) ?></p>
        </footer>
    </div>

</div> <!-- end page-wrap -->
</body>
</html>
