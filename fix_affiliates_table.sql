-- Add missing additional_info column to affiliates table
ALTER TABLE affiliates ADD COLUMN additional_info LONGTEXT NULL AFTER payment_details;

-- Update the column comment for clarity
ALTER TABLE affiliates MODIFY COLUMN additional_info LONGTEXT NULL COMMENT 'JSON data for additional affiliate information';