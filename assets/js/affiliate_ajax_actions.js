/**
 * Affiliate AJAX Actions
 * Handles dynamic data operations without page reload
 */

(function() {
    'use strict';

    // ===================================
    // AJAX Handler Class
    // ===================================
    class AjaxHandler {
        constructor() {
            this.currentData = [];
            this.filteredData = [];
            this.currentPage = 1;
            this.itemsPerPage = 10;
            this.sortColumn = null;
            this.sortDirection = 'asc';
        }

        /**
         * Initialize with data
         */
        setData(data) {
            this.currentData = data;
            this.filteredData = [...data];
            return this;
        }

        /**
         * Filter data by criteria
         */
        filterData(filters) {
            this.filteredData = this.currentData.filter(item => {
                for (let key in filters) {
                    if (filters[key] === '' || filters[key] === 'all') continue;
                    
                    // Handle different filter types
                    if (key === 'search') {
                        // Search across multiple fields
                        const searchTerm = filters[key].toLowerCase();
                        const searchableFields = Object.values(item).join(' ').toLowerCase();
                        if (!searchableFields.includes(searchTerm)) {
                            return false;
                        }
                    } else if (key === 'date_from' || key === 'date_to') {
                        // Date range filter
                        const itemDate = new Date(item.date || item.created_at || item.registered_date);
                        const filterDate = new Date(filters[key]);
                        
                        if (key === 'date_from' && itemDate < filterDate) return false;
                        if (key === 'date_to' && itemDate > filterDate) return false;
                    } else if (key === 'amount_min' || key === 'amount_max') {
                        // Amount range filter
                        const itemAmount = parseFloat(item.amount || item.total_spent || 0);
                        const filterAmount = parseFloat(filters[key]);
                        
                        if (key === 'amount_min' && itemAmount < filterAmount) return false;
                        if (key === 'amount_max' && itemAmount > filterAmount) return false;
                    } else {
                        // Exact match filter
                        if (item[key] !== filters[key]) {
                            return false;
                        }
                    }
                }
                return true;
            });

            // Reset to first page after filtering
            this.currentPage = 1;
            return this;
        }

        /**
         * Sort data by column
         */
        sortData(column, direction = null) {
            // Toggle direction if same column
            if (this.sortColumn === column && direction === null) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = direction || 'asc';
            }

            this.filteredData.sort((a, b) => {
                let aVal = a[column];
                let bVal = b[column];

                // Handle different data types
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }

                // Handle numbers
                if (!isNaN(aVal) && !isNaN(bVal)) {
                    aVal = parseFloat(aVal);
                    bVal = parseFloat(bVal);
                }

                // Handle dates
                if (column.includes('date') || column.includes('time')) {
                    aVal = new Date(aVal).getTime();
                    bVal = new Date(bVal).getTime();
                }

                // Compare
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });

            return this;
        }

        /**
         * Search data across all fields
         */
        searchData(searchTerm) {
            if (!searchTerm || searchTerm.trim() === '') {
                this.filteredData = [...this.currentData];
            } else {
                const term = searchTerm.toLowerCase();
                this.filteredData = this.currentData.filter(item => {
                    return Object.values(item).some(value => {
                        return String(value).toLowerCase().includes(term);
                    });
                });
            }

            // Reset to first page after search
            this.currentPage = 1;
            return this;
        }

        /**
         * Get paginated data
         */
        getPaginatedData() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredData.slice(start, end);
        }

        /**
         * Get pagination info
         */
        getPaginationInfo() {
            const totalItems = this.filteredData.length;
            const totalPages = Math.ceil(totalItems / this.itemsPerPage);
            const start = (this.currentPage - 1) * this.itemsPerPage + 1;
            const end = Math.min(start + this.itemsPerPage - 1, totalItems);

            return {
                currentPage: this.currentPage,
                totalPages: totalPages,
                totalItems: totalItems,
                itemsPerPage: this.itemsPerPage,
                start: start,
                end: end,
                hasNext: this.currentPage < totalPages,
                hasPrev: this.currentPage > 1
            };
        }

        /**
         * Go to specific page
         */
        goToPage(page) {
            const info = this.getPaginationInfo();
            if (page >= 1 && page <= info.totalPages) {
                this.currentPage = page;
            }
            return this;
        }

        /**
         * Go to next page
         */
        nextPage() {
            const info = this.getPaginationInfo();
            if (info.hasNext) {
                this.currentPage++;
            }
            return this;
        }

        /**
         * Go to previous page
         */
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
            return this;
        }

        /**
         * Set items per page
         */
        setItemsPerPage(count) {
            this.itemsPerPage = parseInt(count);
            this.currentPage = 1;
            return this;
        }

        /**
         * Load more items (for infinite scroll)
         */
        loadMore() {
            this.itemsPerPage += 10;
            return this;
        }

        /**
         * Reset all filters and sorting
         */
        reset() {
            this.filteredData = [...this.currentData];
            this.currentPage = 1;
            this.sortColumn = null;
            this.sortDirection = 'asc';
            return this;
        }

        /**
         * Get all filtered data (no pagination)
         */
        getAllData() {
            return this.filteredData;
        }

        /**
         * Get statistics
         */
        getStats() {
            return {
                total: this.currentData.length,
                filtered: this.filteredData.length,
                showing: this.getPaginatedData().length
            };
        }
    }

    // ===================================
    // Table Renderer
    // ===================================
    class TableRenderer {
        constructor(tableId, columns) {
            this.table = document.getElementById(tableId);
            this.tbody = this.table ? this.table.querySelector('tbody') : null;
            this.columns = columns;
        }

        /**
         * Render table rows
         */
        render(data) {
            if (!this.tbody) return;

            if (data.length === 0) {
                this.renderEmpty();
                return;
            }

            let html = '';
            data.forEach(row => {
                html += '<tr>';
                this.columns.forEach(col => {
                    html += `<td data-label="${col.label}">${this.formatCell(row[col.key], col)}</td>`;
                });
                html += '</tr>';
            });

            this.tbody.innerHTML = html;
        }

        /**
         * Format cell value based on column type
         */
        formatCell(value, column) {
            if (column.formatter) {
                return column.formatter(value);
            }

            switch (column.type) {
                case 'currency':
                    return formatCurrency(value);
                case 'date':
                    return formatDate(value);
                case 'number':
                    return formatNumber(value);
                case 'badge':
                    return `<span class="badge badge-${column.badgeClass(value)}">${value}</span>`;
                case 'html':
                    return value;
                default:
                    return value;
            }
        }

        /**
         * Render empty state
         */
        renderEmpty() {
            this.tbody.innerHTML = `
                <tr>
                    <td colspan="${this.columns.length}" class="text-center">
                        <div class="empty-state">
                            <i class="fas fa-inbox empty-state-icon"></i>
                            <p class="empty-state-title">Không có dữ liệu</p>
                            <p class="empty-state-description">Không tìm thấy kết quả phù hợp với bộ lọc của bạn.</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    // ===================================
    // Pagination Renderer
    // ===================================
    class PaginationRenderer {
        constructor(containerId) {
            this.container = document.getElementById(containerId);
        }

        /**
         * Render pagination controls
         */
        render(paginationInfo, onPageChange) {
            if (!this.container) return;

            const { currentPage, totalPages, start, end, totalItems, hasNext, hasPrev } = paginationInfo;

            let html = `
                <div class="pagination-container">
                    <div class="pagination-info">
                        Hiển thị ${start} - ${end} trong tổng số ${totalItems} kết quả
                    </div>
                    <div class="pagination">
                        <button class="pagination-btn" ${!hasPrev ? 'disabled' : ''} data-page="prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
            `;

            // Page numbers
            const maxButtons = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
            let endPage = Math.min(totalPages, startPage + maxButtons - 1);

            if (endPage - startPage < maxButtons - 1) {
                startPage = Math.max(1, endPage - maxButtons + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `
                    <button class="pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">
                        ${i}
                    </button>
                `;
            }

            html += `
                        <button class="pagination-btn" ${!hasNext ? 'disabled' : ''} data-page="next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            `;

            this.container.innerHTML = html;

            // Attach event listeners
            this.container.querySelectorAll('.pagination-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const page = e.currentTarget.getAttribute('data-page');
                    if (page && !e.currentTarget.disabled) {
                        onPageChange(page);
                    }
                });
            });
        }
    }

    // ===================================
    // Export to global scope
    // ===================================
    window.AjaxHandler = AjaxHandler;
    window.TableRenderer = TableRenderer;
    window.PaginationRenderer = PaginationRenderer;

    // ===================================
    // Utility: Debounce function
    // ===================================
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    console.log('AJAX Actions Initialized');

})();
