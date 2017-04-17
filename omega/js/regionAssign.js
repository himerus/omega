/**
 * @file
 * Block behaviors.
 */

(function ($, window, Drupal) {
    "use strict";

    // @todo: Make this work like the block.js version.
    /**
     * Move a block in the blocks table between regions via select list.
     *
     * This behavior is dependent on the tableDrag behavior, since it uses the
     * objects initialized in that behavior to update the row.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *   Attaches the tableDrag behaviour for blocks in block administration.
     */
    Drupal.behaviors.omegaRegionDrag = {
        attach: function (context, settings) {
            // tableDrag is required.
            if (typeof Drupal.tableDrag === 'undefined') {
                return;
            }

            /**
             * Function to check empty regions and toggle classes based on this.
             *
             * @param {jQuery} table
             *   The jQuery object representing the table to inspect.
             * @param {jQuery} rowObject
             *   The jQuery object representing the table row.
             */
            function omegaCheckEmptyRegions(table, rowObject) {
                table.find('tr.layout-region--message').each(function () {
                    var $this = $(this);
                    // If the dragged row is in this region, but above the message row,
                    // swap it down one space.
                    if ($this.prev('tr').get(0) === rowObject.element) {
                        // Prevent a recursion problem when using the keyboard to move rows
                        // up.
                        if ((rowObject.method !== 'keyboard' || rowObject.direction === 'down')) {
                            rowObject.swap('after', this);
                        }
                    }
                    // This region has become empty.
                    if ($this.next('tr').is(':not(.draggable)') || $this.next('tr').length === 0) {
                        $this.removeClass('layout-region--populated').addClass('layout-region--empty');
                    }
                    // This region has become populated.
                    else if ($this.is('.layout-region--empty')) {
                        $this.removeClass('layout-region--empty').addClass('layout-region--populated');
                    }
                });
            }

            /**
             * Function to update the last placed row with the correct classes.
             *
             * @param {jQuery} table
             *   The jQuery object representing the table to inspect.
             * @param {jQuery} rowObject
             *   The jQuery object representing the table row.
             */
            function omegaUpdateLastPlaced(table, rowObject) {
                // Remove the color-success class from new block if applicable.
                table.find('.color-success').removeClass('color-success');

                var $rowObject = $(rowObject);
                if (!$rowObject.is('.drag-previous')) {
                    table.find('.drag-previous').removeClass('drag-previous');
                    $rowObject.addClass('drag-previous');
                }
            }

            /**
             * Update block weights in the given region.
             *
             * @param {jQuery} table
             *   Table with draggable items.
             * @param {string} region
             *   Machine name of region containing blocks to update.
             */
            function omegaUpdateBlockWeights(table, region) {
                // Calculate minimum weight.
                var weight = -Math.round(table.find('.draggable').length / 2);
                // Update the block weights.
                table.find('.layout--' + region + '--message').nextUntil('.region-title')
                    .find('select.layout-weight').val(function () {
                    // Increment the weight before assigning it to prevent using the
                    // absolute minimum available weight. This way we always have an
                    // unused upper and lower bound, which makes manually setting the
                    // weights easier for users who prefer to do it that way.
                    return ++weight;
                });
            }

            var tables = $('[id$="theme-region-assignment"]');

            $.each(tables, function(id, table){
                // Get the blocks tableDrag object.
                table = $(this);

                //omegaCheckEmptyRegions($(table), $(table).find('tr:eq(0)'));
                var tableId = table.attr('id');
                //console.log('tableId: ' + tableId);
                //console.log('-----------------------------------');

                // Check the unassigned region.
                // Modify empty regions with added or removed fields.
                omegaCheckEmptyRegions(table, '');

                var tableDrag = Drupal.tableDrag[tableId];
                // Add a handler for when a row is swapped, update empty regions.
                tableDrag.row.prototype.onSwap = function (swappedRow) {
                    omegaCheckEmptyRegions(table, this);
                    omegaUpdateLastPlaced(table, this);
                };

                // Add a handler so when a row is dropped, update fields dropped into
                // new regions.
                tableDrag.onDrop = function () {
                    var dragObject = this;
                    var $rowElement = $(dragObject.rowObject.element);
                    // Use "region-message" row instead of "region" row because
                    // "region-{region_name}-message" is less prone to regexp match errors.
                    var regionRow = $rowElement.prevAll('tr.layout-region--message').get(0);
                    var regionName = regionRow.className.replace(/([^ ]+[ ]+)*layout-region--([^ ]+)--message([ ]+[^ ]+)*/, '$2');
                    var regionField = $rowElement.find('select.layout-region-select');
                    // Check whether the newly picked region is available for this block.
                    if (regionField.find('option[value=' + regionName + ']').length === 0) {
                        // If not, alert the user and keep the block in its old region
                        // setting.
                        window.alert(Drupal.t('The region cannot be placed in this region.'));
                        // Simulate that there was a selected element change, so the row is
                        // put back to from where the user tried to drag it.
                        regionField.trigger('change');
                    }

                    // Update region and weight fields if the region has been changed.
                    if (!regionField.is('.layout-region--' + regionName)) {
                        var weightField = $rowElement.find('select.layout-weight');
                        var oldRegionName = weightField[0].className.replace(/([^ ]+[ ]+)*layout-weight-([^ ]+)([ ]+[^ ]+)*/, '$2');
                        regionField.removeClass('layout-region--' + oldRegionName).addClass('layout-region--' + regionName);
                        weightField.removeClass('layout-weight--' + oldRegionName).addClass('layout-weight--' + regionName);
                        regionField.val(regionName);
                    }

                    omegaUpdateBlockWeights(table, regionName);
                };

                // Add the behavior to each region select list.
                $(context).find('select.layout-region-select').once('layout-region-select')
                    .on('change', function (event) {
                        // Make our new row and select field.
                        var parentTable = $(this).closest('table');
                        var row = $(this).closest('tr');
                        var select = $(this);
                        // Find the correct region and insert the row as the last in the
                        // region.
                        tableDrag.rowObject = new tableDrag.row(row[0]);
                        var region_message = parentTable.find('.layout-region--' + select[0].value + '--message');
                        var region_items = region_message.nextUntil('.layout-region--message, .layout-region--title');
                        if (region_items.length) {
                            region_items.last().after(row);
                        }
                        // We found that region_message is the last row.
                        else {
                            region_message.after(row);
                        }
                        omegaUpdateBlockWeights(parentTable, select[0].value);
                        // Modify empty regions with added or removed fields.
                        omegaCheckEmptyRegions(parentTable, tableDrag.rowObject);
                        // Update last placed block indication.
                        omegaUpdateLastPlaced(parentTable, row);
                        // Show unsaved changes warning.
                        if (!tableDrag.changed) {
                            $(Drupal.theme('tableDragChangedWarning')).insertBefore(tableDrag.parentTable).hide().fadeIn('slow');
                            tableDrag.changed = true;
                        }
                        // Remove focus from selectbox.
                        select.trigger('blur');
                    });
            })

        }
    };

})(jQuery, window, Drupal);
