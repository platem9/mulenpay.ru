from setuptools import setup, find_packages

setup(
    name="mulen-pay-sdk",
    version="0.1.0",
    packages=find_packages(),
    install_requires=[
        "requests",
    ],
    author="Your Name",
    author_email="your.email@example.com",
    description="Python SDK for Mulen Pay API",
    long_description=open('README.md').read(),
    long_description_content_type="text/markdown",
    url="https://github.com/your-username/mulen-pay-sdk",
)
