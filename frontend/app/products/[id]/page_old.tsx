'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { useAuth } from '@/lib/AuthContext';
import { productAPI, Product } from '@/lib/api';

export default function ProductDetailPage() {
  const { token } = useAuth();
  const router = useRouter();
  const params = useParams();
  const id = params.id as string;

  const [product, setProduct] = useState<Product | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    // Redirect if not authenticated
    if (!token) {
      router.push('/login');
      return;
    }

    fetchProduct();
  }, [id, token, router]);

  const fetchProduct = async () => {
    setLoading(true);
    setError('');
    try {
      const data = await productAPI.getById(parseInt(id));
      setProduct(data.product);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to fetch product');
    } finally {
      setLoading(false);
    }
  };

  const handleBack = () => {
    router.push('/dashboard');
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-lg text-gray-600">Loading product...</div>
      </div>
    );
  }

  if (error || !product) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="text-lg text-red-600 mb-4">
            {error || 'Product not found'}
          </div>
          <button
            onClick={handleBack}
            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Back to Dashboard
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <button
            onClick={handleBack}
            className="text-blue-600 hover:text-blue-800 flex items-center"
          >
            <span className="mr-2">←</span> Back to Dashboard
          </button>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="bg-white rounded-lg shadow-lg overflow-hidden">
          <div className="md:flex">
            {/* Product Image */}
            <div className="md:w-1/2">
              <div className="h-96 bg-gray-200 flex items-center justify-center">
                {product.image ? (
                  <img
                    src={`http://localhost:8000/storage/${product.image}`}
                    alt={product.name}
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <span className="text-gray-400 text-xl">No Image Available</span>
                )}
              </div>
            </div>

            {/* Product Details */}
            <div className="md:w-1/2 p-8">
              <div className="uppercase tracking-wide text-sm text-blue-600 font-semibold">
                {product.category}
              </div>
              <h1 className="mt-2 text-3xl font-bold text-gray-900">
                {product.name}
              </h1>

              <div className="mt-4 flex items-center">
                <span className="text-4xl font-bold text-blue-600">
                  ${parseFloat(product.price.toString()).toFixed(2)}
                </span>
                <span className="ml-4 text-xl text-yellow-600">
                  ⭐ {parseFloat(product.rating.toString()).toFixed(1)} / 5.0
                </span>
              </div>

              <div className="mt-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-2">
                  Description
                </h2>
                <p className="text-gray-700 leading-relaxed">
                  {product.description || 'No description available.'}
                </p>
              </div>

              <div className="mt-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span className="text-gray-600">Product ID:</span>
                  <span className="ml-2 font-medium">{product.id}</span>
                </div>
                <div>
                  <span className="text-gray-600">Category:</span>
                  <span className="ml-2 font-medium">{product.category}</span>
                </div>
                <div>
                  <span className="text-gray-600">Created:</span>
                  <span className="ml-2 font-medium">
                    {new Date(product.created_at).toLocaleDateString()}
                  </span>
                </div>
                <div>
                  <span className="text-gray-600">Updated:</span>
                  <span className="ml-2 font-medium">
                    {new Date(product.updated_at).toLocaleDateString()}
                  </span>
                </div>
              </div>

              <div className="mt-8">
                <button
                  onClick={handleBack}
                  className="w-full px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium"
                >
                  Back to Products
                </button>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
