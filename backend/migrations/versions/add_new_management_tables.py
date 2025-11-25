"""Add new management tables

Revision ID: add_new_management_tables
Revises: 
Create Date: 2024-11-24

"""
from alembic import op
import sqlalchemy as sa
from sqlalchemy.dialects import postgresql

# revision identifiers, used by Alembic.
revision = 'add_new_management_tables'
down_revision = None
branch_labels = None
depends_on = None


def upgrade():
    # Create roles table
    op.create_table(
        'roles',
        sa.Column('id', sa.Integer(), nullable=False),
        sa.Column('name', sa.String(length=50), nullable=False),
        sa.Column('display_name', sa.String(length=100), nullable=False),
        sa.Column('description', sa.Text(), nullable=True),
        sa.Column('permissions', sa.JSON(), nullable=False),
        sa.Column('is_system', sa.Boolean(), default=False),
        sa.Column('is_active', sa.Boolean(), default=True),
        sa.Column('created_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.Column('updated_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.PrimaryKeyConstraint('id')
    )
    op.create_index(op.f('ix_roles_name'), 'roles', ['name'], unique=True)

    # Create activity_logs table
    op.create_table(
        'activity_logs',
        sa.Column('id', sa.Integer(), nullable=False),
        sa.Column('user_id', sa.Integer(), nullable=False),
        sa.Column('action', sa.String(length=100), nullable=False),
        sa.Column('module', sa.String(length=50), nullable=False),
        sa.Column('description', sa.Text(), nullable=True),
        sa.Column('ip_address', sa.String(length=45), nullable=True),
        sa.Column('user_agent', sa.String(length=255), nullable=True),
        sa.Column('metadata', sa.JSON(), nullable=True),
        sa.Column('status', sa.String(length=20), nullable=False, default='success'),
        sa.Column('error_message', sa.Text(), nullable=True),
        sa.Column('created_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.PrimaryKeyConstraint('id'),
        sa.ForeignKeyConstraint(['user_id'], ['users.id'], )
    )
    op.create_index(op.f('ix_activity_logs_user_id'), 'activity_logs', ['user_id'])
    op.create_index(op.f('ix_activity_logs_action'), 'activity_logs', ['action'])
    op.create_index(op.f('ix_activity_logs_module'), 'activity_logs', ['module'])
    op.create_index(op.f('ix_activity_logs_created_at'), 'activity_logs', ['created_at'])

    # Create leave_configs table
    op.create_table(
        'leave_configs',
        sa.Column('id', sa.Integer(), nullable=False),
        sa.Column('leave_type', sa.Integer(), nullable=False),
        sa.Column('leave_type_name', sa.String(length=50), nullable=False),
        sa.Column('annual_days', sa.Integer(), nullable=False, default=0),
        sa.Column('max_consecutive_days', sa.Integer(), nullable=True),
        sa.Column('min_days_notice', sa.Integer(), default=0),
        sa.Column('can_carry_forward', sa.Boolean(), default=False),
        sa.Column('max_carry_forward_days', sa.Integer(), default=0),
        sa.Column('is_accrued', sa.Boolean(), default=False),
        sa.Column('accrual_rate', sa.Numeric(5, 2), default=0),
        sa.Column('requires_approval', sa.Boolean(), default=True),
        sa.Column('is_paid', sa.Boolean(), default=True),
        sa.Column('gender_specific', sa.String(length=1), nullable=True),
        sa.Column('requires_documentation', sa.Boolean(), default=False),
        sa.Column('documentation_after_days', sa.Integer(), default=0),
        sa.Column('is_active', sa.Boolean(), default=True),
        sa.Column('description', sa.Text(), nullable=True),
        sa.Column('created_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.Column('updated_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.PrimaryKeyConstraint('id')
    )
    op.create_index(op.f('ix_leave_configs_leave_type'), 'leave_configs', ['leave_type'], unique=True)

    # Create salary_structures table
    op.create_table(
        'salary_structures',
        sa.Column('id', sa.Integer(), nullable=False),
        sa.Column('employee_id', sa.Integer(), nullable=False),
        sa.Column('basic_salary', sa.Numeric(10, 2), nullable=False),
        sa.Column('house_allowance', sa.Numeric(10, 2), default=0),
        sa.Column('transport_allowance', sa.Numeric(10, 2), default=0),
        sa.Column('medical_allowance', sa.Numeric(10, 2), default=0),
        sa.Column('communication_allowance', sa.Numeric(10, 2), default=0),
        sa.Column('meal_allowance', sa.Numeric(10, 2), default=0),
        sa.Column('other_allowances', sa.Numeric(10, 2), default=0),
        sa.Column('nssf_rate', sa.Numeric(5, 2), default=6.00),
        sa.Column('nhif_amount', sa.Numeric(10, 2), default=0),
        sa.Column('payment_frequency', sa.String(length=20), default='monthly'),
        sa.Column('payment_method', sa.String(length=50), default='bank_transfer'),
        sa.Column('is_active', sa.Boolean(), default=True),
        sa.Column('effective_from', sa.DateTime(timezone=True), nullable=False),
        sa.Column('effective_to', sa.DateTime(timezone=True), nullable=True),
        sa.Column('notes', sa.Text(), nullable=True),
        sa.Column('created_by', sa.Integer(), nullable=True),
        sa.Column('created_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.Column('updated_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.PrimaryKeyConstraint('id'),
        sa.ForeignKeyConstraint(['employee_id'], ['users.id'], )
    )
    op.create_index(op.f('ix_salary_structures_employee_id'), 'salary_structures', ['employee_id'])

    # Create payments table
    op.create_table(
        'payments',
        sa.Column('id', sa.Integer(), nullable=False),
        sa.Column('employee_id', sa.Integer(), nullable=False),
        sa.Column('payroll_id', sa.Integer(), nullable=True),
        sa.Column('amount', sa.Numeric(10, 2), nullable=False),
        sa.Column('payment_date', sa.Date(), nullable=False),
        sa.Column('payment_type', sa.String(length=50), nullable=False),
        sa.Column('payment_method', sa.String(length=50), nullable=False),
        sa.Column('reference_number', sa.String(length=100), nullable=True),
        sa.Column('transaction_id', sa.String(length=100), nullable=True),
        sa.Column('bank_name', sa.String(length=100), nullable=True),
        sa.Column('account_number', sa.String(length=50), nullable=True),
        sa.Column('status', sa.SmallInteger(), default=1),
        sa.Column('description', sa.Text(), nullable=True),
        sa.Column('notes', sa.Text(), nullable=True),
        sa.Column('processed_by', sa.Integer(), nullable=True),
        sa.Column('created_by', sa.Integer(), nullable=True),
        sa.Column('created_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.Column('updated_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.Column('completed_at', sa.DateTime(timezone=True), nullable=True),
        sa.PrimaryKeyConstraint('id'),
        sa.ForeignKeyConstraint(['employee_id'], ['users.id'], ),
        sa.ForeignKeyConstraint(['payroll_id'], ['payroll.id'], ),
        sa.ForeignKeyConstraint(['processed_by'], ['users.id'], )
    )
    op.create_index(op.f('ix_payments_employee_id'), 'payments', ['employee_id'])
    op.create_index(op.f('ix_payments_payroll_id'), 'payments', ['payroll_id'])
    op.create_index(op.f('ix_payments_payment_date'), 'payments', ['payment_date'])
    op.create_index(op.f('ix_payments_reference_number'), 'payments', ['reference_number'], unique=True)

    # Create user_settings table
    op.create_table(
        'user_settings',
        sa.Column('id', sa.Integer(), nullable=False),
        sa.Column('user_id', sa.Integer(), nullable=False),
        sa.Column('email_notifications', sa.Boolean(), default=True),
        sa.Column('sms_notifications', sa.Boolean(), default=False),
        sa.Column('push_notifications', sa.Boolean(), default=True),
        sa.Column('notify_leave_approval', sa.Boolean(), default=True),
        sa.Column('notify_payroll', sa.Boolean(), default=True),
        sa.Column('notify_attendance', sa.Boolean(), default=True),
        sa.Column('notify_announcements', sa.Boolean(), default=True),
        sa.Column('theme', sa.String(length=20), default='light'),
        sa.Column('language', sa.String(length=10), default='en'),
        sa.Column('timezone', sa.String(length=50), default='UTC'),
        sa.Column('date_format', sa.String(length=20), default='YYYY-MM-DD'),
        sa.Column('dashboard_layout', sa.JSON(), nullable=True),
        sa.Column('profile_visibility', sa.String(length=20), default='all'),
        sa.Column('show_email', sa.Boolean(), default=True),
        sa.Column('show_phone', sa.Boolean(), default=True),
        sa.Column('two_factor_enabled', sa.Boolean(), default=False),
        sa.Column('two_factor_method', sa.String(length=20), nullable=True),
        sa.Column('session_timeout', sa.Integer(), default=30),
        sa.Column('created_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.Column('updated_at', sa.DateTime(timezone=True), server_default=sa.text('now()'), nullable=False),
        sa.PrimaryKeyConstraint('id'),
        sa.ForeignKeyConstraint(['user_id'], ['users.id'], )
    )
    op.create_index(op.f('ix_user_settings_user_id'), 'user_settings', ['user_id'], unique=True)


def downgrade():
    op.drop_table('user_settings')
    op.drop_table('payments')
    op.drop_table('salary_structures')
    op.drop_table('leave_configs')
    op.drop_table('activity_logs')
    op.drop_table('roles')
